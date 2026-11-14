<?php

namespace App\Services;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Throwable;
use ZipArchive;

class BackupService
{
    public const DIRECTORY = 'backups';

    public const SIGNATURE_PREFIX = '-- EstateFlow-Signature: ';

    /** Where each folder in a backup zip goes back to. */
    private const FILE_FOLDERS = ['private' => 'local', 'public' => 'public', 'supabase' => 'supabase'];

    /** @var array<string, string> sha256 of every file added to the current backup, by its path in the zip. */
    private array $fileHashes = [];

    // Short-lived data that is rebuilt on its own. Reset tokens must never leave the system.
    private const EXCLUDED_TABLES = ['sessions', 'cache', 'cache_locks', 'jobs', 'job_batches', 'failed_jobs', 'password_reset_tokens'];

    /** @var array<string, array<int, array<string, mixed>>> */
    private array $foreignKeys = [];

    /**
     * @return Collection<int, array{name: string, size: int, created_at: int}>
     */
    public function all(): Collection
    {
        $disk = Storage::disk('local');

        return collect($disk->files(self::DIRECTORY))
            ->filter(fn (string $file) => str_ends_with($file, '.zip'))
            ->map(fn (string $file) => [
                'name' => basename($file),
                'size' => $disk->size($file),
                'created_at' => $disk->lastModified($file),
            ])
            ->sortByDesc('created_at')
            ->values();
    }

    public function exists(string $name): bool
    {
        return Storage::disk('local')->exists($this->path($name));
    }

    public function path(string $name): string
    {
        return self::DIRECTORY.'/'.basename($name);
    }

    /**
     * Streams only the database part of a backup, as a .sql file.
     */
    public function streamSql(string $name): void
    {
        $zip = new ZipArchive;

        if ($zip->open(Storage::disk('local')->path($this->path($name))) !== true) {
            throw new RuntimeException('The backup file could not be opened.');
        }

        $stream = $zip->getStream('database.sql');

        if ($stream === false) {
            $zip->close();

            throw new RuntimeException('This backup has no database.sql file.');
        }

        fpassthru($stream);
        fclose($stream);
        $zip->close();
    }

    public function hasSql(string $name): bool
    {
        $zip = new ZipArchive;

        if ($zip->open(Storage::disk('local')->path($this->path($name))) !== true) {
            return false;
        }

        $found = $zip->locateName('database.sql') !== false;
        $zip->close();

        return $found;
    }

    /**
     * Replaces every record with the ones in an EstateFlow .sql backup, in one transaction:
     * if any row fails to load, nothing changes. Uploaded files are not touched.
     *
     * @throws InvalidBackupException when the file is not an unedited EstateFlow backup.
     */
    public function restore(string $sql): void
    {
        set_time_limit(0);

        $body = $this->verifiedBody($sql);

        // The file wraps itself in BEGIN and COMMIT for manual use; here the transaction below does that job.
        $begin = strpos($body, "\nBEGIN;\n");
        $commit = strrpos($body, "\nCOMMIT;\n");

        if ($begin === false || $commit === false || $commit < $begin) {
            throw new InvalidBackupException('This file is not a complete EstateFlow backup.');
        }

        $statements = substr($body, 0, $begin)."\n".substr($body, $begin + 8, $commit - $begin - 8);
        $tables = $this->inLoadOrder($this->tables())->reject(fn (string $table) => $table === 'migrations')->values();

        DB::transaction(function () use ($tables, $statements) {
            if (DB::connection()->getDriverName() === 'pgsql') {
                DB::unprepared('TRUNCATE TABLE '.$tables->map(fn (string $table) => $this->quoteName($table))->implode(', ').' CASCADE');
            } else {
                foreach ($tables->reverse() as $table) {
                    DB::table($table)->delete();
                }
            }

            DB::unprepared($statements);
        });

        // The cached logo and other settings may have changed with the restore.
        \App\Models\Setting::forgetCache();
    }

    /**
     * Restores a whole backup zip: every record, then every uploaded file. Everything is
     * checked before anything changes; the records load all or nothing.
     *
     * @return array{files_restored: int, files_failed: array<int, string>}
     *
     * @throws InvalidBackupException when the zip is not an unedited EstateFlow backup.
     */
    public function restoreArchive(string $zipPath): array
    {
        set_time_limit(0);

        $zip = new ZipArchive;

        if ($zip->open($zipPath) !== true) {
            throw new InvalidBackupException('This file is not a readable backup zip.');
        }

        try {
            $files = $this->verifiedFiles($zip);
            $sql = $zip->getFromName('database.sql');

            if ($sql === false) {
                throw new InvalidBackupException('This backup zip has no database.sql file.');
            }

            $this->restore($sql);

            $restored = 0;
            $failed = [];

            foreach ($files as $entry => [$disk, $path]) {
                try {
                    $stream = $zip->getStream($entry);
                    Storage::disk($disk)->writeStream($path, $stream, $disk === 'supabase' ? ['visibility' => 'public'] : []);
                    is_resource($stream) && fclose($stream);
                    $restored++;
                } catch (Throwable $e) {
                    report($e);
                    $failed[] = $entry;
                }
            }

            return ['files_restored' => $restored, 'files_failed' => $failed];
        } finally {
            $zip->close();
        }
    }

    /**
     * Checks the signed manifest and every file against it, before anything is restored.
     *
     * @return array<string, array{0: string, 1: string}> Zip entry => [disk, path on that disk].
     */
    private function verifiedFiles(ZipArchive $zip): array
    {
        $manifest = $zip->getFromName('manifest.json');
        $signature = $zip->getFromName('manifest.sig');

        if ($manifest === false || $signature === false) {
            throw new InvalidBackupException('This zip was made before importing was supported, or it is not an EstateFlow backup. Create a new backup and use that zip.');
        }

        if (! hash_equals(hash_hmac('sha256', $manifest, $this->signingKey()), trim($signature))) {
            throw new InvalidBackupException('This backup zip was changed after it was made, or it came from a different EstateFlow installation, so it was not imported.');
        }

        $files = [];

        foreach ((array) (json_decode($manifest, true)['file_hashes'] ?? []) as $entry => $hash) {
            [$disk, $path] = $this->fileTarget((string) $entry);
            $stream = $zip->getStream((string) $entry);

            if ($stream === false) {
                throw new InvalidBackupException("This backup zip is missing {$entry}.");
            }

            $context = hash_init('sha256');
            hash_update_stream($context, $stream);
            fclose($stream);

            if (! hash_equals((string) $hash, hash_final($context))) {
                throw new InvalidBackupException("A file in this backup zip was changed after it was made ({$entry}), so nothing was imported.");
            }

            $files[(string) $entry] = [$disk, $path];
        }

        return $files;
    }

    /**
     * Only files inside EstateFlow's own storage folders can be restored, never anywhere else on the computer.
     *
     * @return array{0: string, 1: string}
     */
    private function fileTarget(string $entry): array
    {
        $parts = explode('/', $entry, 3);
        $path = $parts[2] ?? '';
        $unsafe = $path === ''
            || str_contains($path, '\\') || str_contains($path, ':') || str_contains($path, "\0")
            || str_starts_with($path, '/')
            || in_array('..', explode('/', $path), true) || in_array('.', explode('/', $path), true);

        if (($parts[0] ?? '') !== 'files' || ! isset(self::FILE_FOLDERS[$parts[1] ?? '']) || $unsafe || str_starts_with($path, self::DIRECTORY.'/')) {
            throw new InvalidBackupException("This backup zip contains a file outside EstateFlow's storage ({$entry}), so nothing was imported.");
        }

        return [self::FILE_FOLDERS[$parts[1]], $path];
    }

    private function verifiedBody(string $sql): string
    {
        $sql = str_replace("\r\n", "\n", $sql);
        $end = strrpos(rtrim($sql, "\n"), "\n");
        $lastLine = trim(substr($sql, $end === false ? 0 : $end + 1));

        if (! str_starts_with($sql, '-- EstateFlow database backup') || ! str_starts_with($lastLine, self::SIGNATURE_PREFIX)) {
            throw new InvalidBackupException('This is not an EstateFlow backup file, or it was made before importing was supported. Create a new backup and use its .sql file.');
        }

        $body = substr($sql, 0, $end + 1);
        $expected = hash_hmac('sha256', $body, $this->signingKey());

        if (! hash_equals($expected, substr($lastLine, strlen(self::SIGNATURE_PREFIX)))) {
            throw new InvalidBackupException('This backup file was changed after it was made, or it came from a different EstateFlow installation, so it was not imported.');
        }

        return $body;
    }

    private function signingKey(): string
    {
        return (string) config('app.key');
    }

    public function delete(string $name): void
    {
        Storage::disk('local')->delete($this->path($name));
    }

    /**
     * Writes every application table and stored file into one zip on the private disk.
     */
    public function create(): string
    {
        set_time_limit(0);

        $disk = Storage::disk('local');
        $disk->makeDirectory(self::DIRECTORY);

        $name = 'estateflow-backup-'.now()->format('Y-m-d-His').'-'.Str::lower(Str::random(6)).'.zip';
        $zipPath = $disk->path($this->path($name));
        $workDir = storage_path('app/backup-work-'.Str::random(8));
        File::ensureDirectoryExists($workDir);

        $zip = new ZipArchive;

        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::EXCL) !== true) {
            File::deleteDirectory($workDir);

            throw new RuntimeException('The backup file could not be created.');
        }

        $this->fileHashes = [];

        try {
            $tables = $this->addDatabase($zip, $workDir);
            $files = $this->addStoredFiles($zip);
            [$remoteFiles, $warnings] = $this->addRemoteFiles($zip, $workDir);

            $manifest = json_encode([
                'application' => config('app.name'),
                'created_at' => now()->toIso8601String(),
                'database_driver' => DB::connection()->getDriverName(),
                'restore_file' => 'database.sql',
                'tables' => $tables,
                'excluded_tables' => self::EXCLUDED_TABLES,
                'stored_files' => $files,
                'remote_files' => $remoteFiles,
                'warnings' => $warnings,
                'file_hashes' => $this->fileHashes,
            ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            $zip->addFromString('manifest.json', $manifest);
            $zip->addFromString('manifest.sig', hash_hmac('sha256', $manifest, $this->signingKey()));

            if (! $zip->close()) {
                throw new RuntimeException('The backup file could not be finished.');
            }
        } catch (Throwable $e) {
            File::delete($zipPath);

            throw $e;
        } finally {
            File::deleteDirectory($workDir);
        }

        return $name;
    }

    /**
     * Each table is written row by row, so large tables stay out of memory: as a JSON file
     * that is easy to read, and into one database.sql that can be loaded back in.
     *
     * @return array<string, int> Row count for each table.
     */
    private function addDatabase(ZipArchive $zip, string $workDir): array
    {
        $counts = [];
        $tables = $this->inLoadOrder($this->tables());
        $sqlPath = "{$workDir}/database.sql";
        $sql = fopen($sqlPath, 'w');
        $signature = hash_init('sha256', HASH_HMAC, $this->signingKey());
        $writeSql = function (string $text) use ($sql, $signature): void {
            fwrite($sql, $text);
            hash_update($signature, $text);
        };

        $writeSql($this->sqlHeader());

        foreach ($tables as $table) {
            $path = "{$workDir}/{$table}.json";
            $handle = fopen($path, 'w');
            $count = 0;
            // The schema comes from running the migrations, which also fills this table.
            $includeInSql = $table !== 'migrations';

            fwrite($handle, '[');

            foreach ($this->rows($table)->cursor() as $row) {
                fwrite($handle, ($count++ ? ",\n" : "\n").json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE));

                if ($includeInSql) {
                    $writeSql($this->insertStatement($table, (array) $row));
                }
            }

            fwrite($handle, "\n]\n");
            fclose($handle);

            $zip->addFile($path, "database/{$table}.json");
            $counts[$table] = $count;
        }

        $writeSql($this->sqlFooter($tables));
        fwrite($sql, self::SIGNATURE_PREFIX.hash_final($signature)."\n");
        fclose($sql);

        $zip->addFile($sqlPath, 'database.sql');

        return $counts;
    }

    /**
     * Rows that point at another row in the same table come after the ones they need,
     * such as agents after their brokers.
     */
    private function rows(string $table): Builder
    {
        $query = DB::table($table);

        foreach ($this->foreignKeys($table) as $key) {
            if ($key['foreign_table'] === $table) {
                $query->orderByRaw($this->quoteName($key['columns'][0]).' is not null');
            }
        }

        return $query;
    }

    /**
     * Tables come after the tables they refer to, so a restore never breaks a foreign key.
     */
    private function inLoadOrder(Collection $tables): Collection
    {
        $needs = $tables->mapWithKeys(fn (string $table) => [
            $table => collect($this->foreignKeys($table))
                ->pluck('foreign_table')
                ->reject(fn (string $other) => $other === $table)
                ->intersect($tables)
                ->unique()
                ->all(),
        ]);

        $ordered = collect();

        while ($needs->isNotEmpty()) {
            $ready = $needs->filter(fn (array $others) => array_diff($others, $ordered->all()) === [])->keys();

            // Tables that refer to each other cannot be ordered, so they keep their place.
            if ($ready->isEmpty()) {
                return $ordered->concat($needs->keys())->values();
            }

            $ordered = $ordered->concat($ready);
            $needs = $needs->except($ready->all());
        }

        return $ordered->values();
    }

    private function foreignKeys(string $table): array
    {
        return $this->foreignKeys[$table] ??= Schema::getForeignKeys($table);
    }

    private function sqlHeader(): string
    {
        $lines = [
            '-- EstateFlow database backup, '.now()->toDateTimeString(),
            '-- Load this into an empty database that already has the tables:',
            '--   1. Run: php artisan migrate',
            '--   2. Run this file. It stops without changing anything if a row cannot be loaded.',
            '',
        ];

        if (DB::connection()->getDriverName() === 'pgsql') {
            $lines[] = "SET client_encoding = 'UTF8';";
            $lines[] = 'SET standard_conforming_strings = on;';
        }

        $lines[] = 'BEGIN;';

        return implode("\n", $lines)."\n\n";
    }

    /**
     * Restored rows keep their ids, so each id counter moves past them. Otherwise the next
     * new record would try to reuse an id that is already taken.
     */
    private function sqlFooter(Collection $tables): string
    {
        $lines = [];

        if (DB::connection()->getDriverName() === 'pgsql') {
            foreach ($tables->reject(fn (string $table) => $table === 'migrations') as $table) {
                if (Schema::hasColumn($table, 'id')) {
                    $name = str_replace("'", "''", $this->quoteName($table));
                    $lines[] = "SELECT setval(pg_get_serial_sequence('{$name}', 'id'), COALESCE(MAX(id), 1), MAX(id) IS NOT NULL) FROM {$this->quoteName($table)};";
                }
            }
        }

        $lines[] = 'COMMIT;';

        return "\n".implode("\n", $lines)."\n";
    }

    private function insertStatement(string $table, array $row): string
    {
        $columns = implode(', ', array_map(fn (string $column) => $this->quoteName($column), array_keys($row)));
        $values = implode(', ', array_map(fn ($value) => $this->sqlValue($value), $row));

        return "INSERT INTO {$this->quoteName($table)} ({$columns}) VALUES ({$values});\n";
    }

    private function sqlValue(mixed $value): string
    {
        return match (true) {
            $value === null => 'NULL',
            is_bool($value) => $value ? 'TRUE' : 'FALSE',
            is_int($value) => (string) $value,
            is_float($value) => json_encode($value),
            is_resource($value) => DB::getPdo()->quote(stream_get_contents($value)),
            default => DB::getPdo()->quote((string) $value),
        };
    }

    private function quoteName(string $name): string
    {
        return '"'.str_replace('"', '""', $name).'"';
    }

    private function tables(): Collection
    {
        $tables = collect(Schema::getTables());

        // Supabase keeps its own auth and storage schemas in the same database.
        if (DB::connection()->getDriverName() === 'pgsql') {
            $tables = $tables->where('schema', config('database.connections.pgsql.search_path', 'public'));
        }

        return $tables->pluck('name')
            ->reject(fn (string $table) => in_array($table, self::EXCLUDED_TABLES, true) || str_starts_with($table, 'sqlite_'))
            ->sort()
            ->values();
    }

    private function addStoredFiles(ZipArchive $zip): int
    {
        $count = 0;

        foreach (['local' => 'private', 'public' => 'public'] as $diskName => $folder) {
            $disk = Storage::disk($diskName);

            foreach ($disk->allFiles() as $file) {
                if (str_starts_with($file, self::DIRECTORY.'/') || basename($file) === '.gitignore') {
                    continue;
                }

                $zip->addFile($disk->path($file), "files/{$folder}/{$file}");
                $this->fileHashes["files/{$folder}/{$file}"] = hash_file('sha256', $disk->path($file));
                $count++;
            }
        }

        return $count;
    }

    /**
     * Property photos live in Supabase storage. If it cannot be reached the backup still
     * finishes, and the manifest and the admin both say what is missing.
     *
     * @return array{0: int, 1: array<int, string>}
     */
    private function addRemoteFiles(ZipArchive $zip, string $workDir): array
    {
        $count = 0;
        $warnings = [];

        try {
            $disk = Storage::disk('supabase');

            foreach ($disk->allFiles() as $file) {
                $path = $workDir.'/remote-'.$count;
                File::put($path, $disk->get($file));
                $zip->addFile($path, "files/supabase/{$file}");
                $this->fileHashes["files/supabase/{$file}"] = hash_file('sha256', $path);
                $count++;
            }
        } catch (Throwable $e) {
            report($e);
            $warnings[] = 'Some property photos in Supabase storage could not be included.';
        }

        return [$count, $warnings];
    }
}
