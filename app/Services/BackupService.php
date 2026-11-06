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

        try {
            $tables = $this->addDatabase($zip, $workDir);
            $files = $this->addStoredFiles($zip);
            [$remoteFiles, $warnings] = $this->addRemoteFiles($zip, $workDir);

            $zip->addFromString('manifest.json', json_encode([
                'application' => config('app.name'),
                'created_at' => now()->toIso8601String(),
                'database_driver' => DB::connection()->getDriverName(),
                'restore_file' => 'database.sql',
                'tables' => $tables,
                'excluded_tables' => self::EXCLUDED_TABLES,
                'stored_files' => $files,
                'remote_files' => $remoteFiles,
                'warnings' => $warnings,
            ], JSON_PRETTY_PRINT));

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

        fwrite($sql, $this->sqlHeader());

        foreach ($tables as $table) {
            $path = "{$workDir}/{$table}.json";
            $handle = fopen($path, 'w');
            $count = 0;
            // The schema comes from running the migrations, which also fills this table.
            $writeSql = $table !== 'migrations';

            fwrite($handle, '[');

            foreach ($this->rows($table)->cursor() as $row) {
                fwrite($handle, ($count++ ? ",\n" : "\n").json_encode($row, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_INVALID_UTF8_SUBSTITUTE));

                if ($writeSql) {
                    fwrite($sql, $this->insertStatement($table, (array) $row));
                }
            }

            fwrite($handle, "\n]\n");
            fclose($handle);

            $zip->addFile($path, "database/{$table}.json");
            $counts[$table] = $count;
        }

        fwrite($sql, $this->sqlFooter($tables));
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
                $count++;
            }
        } catch (Throwable $e) {
            report($e);
            $warnings[] = 'Some property photos in Supabase storage could not be included.';
        }

        return [$count, $warnings];
    }
}
