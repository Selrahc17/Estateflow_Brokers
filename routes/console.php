<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('files:make-private', function () {
    $public = Storage::disk('public');
    $private = Storage::disk('local');
    $moved = 0;
    $skipped = 0;

    foreach (['commission-proof', 'chat'] as $folder) {
        foreach ($public->allFiles($folder) as $file) {
            if ($private->exists($file)) {
                $skipped++;
                continue;
            }

            $private->writeStream($file, $public->readStream($file));

            if ($private->size($file) === $public->size($file)) {
                $public->delete($file);
                $moved++;
            }
        }
    }

    $this->info("Moved {$moved} file(s) to private storage. Skipped {$skipped}.");
})->purpose('Move commission proofs and chat photos out of public storage');

Artisan::command('backup:restore {file : Path to an EstateFlow backup .zip, or the name of one on the Backups page} {--force : Skip the confirmation question}', function (\App\Services\BackupService $backups) {
    $file = (string) $this->argument('file');
    $path = is_file($file) ? $file : (Storage::disk('local')->exists($backups->path($file)) ? Storage::disk('local')->path($backups->path($file)) : null);

    if (! $path || ! str_ends_with(strtolower($path), '.zip')) {
        $this->error('Backup zip not found. Give the path to the .zip file downloaded from the Backups page.');

        return 1;
    }

    // Works even on an empty database: the tables are rebuilt first.
    if (! Schema::hasTable('users')) {
        $this->info('The database has no tables. Creating them...');
        $this->call('migrate', ['--force' => true]);
    }

    $hasData = DB::table('users')->exists();

    if ($hasData && ! $this->option('force') && ! $this->confirm('This replaces ALL current records with the ones in the backup. Continue?')) {
        $this->line('Nothing was changed.');

        return 1;
    }

    if ($hasData) {
        $this->info('Saving a safety backup of the current data first...');
        $this->info('Safety backup: '.$backups->create());
    }

    $this->info('Restoring '.basename($path).'...');

    try {
        $result = $backups->restoreArchive($path);
    } catch (\App\Services\InvalidBackupException $e) {
        $this->error($e->getMessage());

        return 1;
    } catch (\Throwable $e) {
        report($e);
        $this->error('The records could not be loaded, so nothing was changed: '.$e->getMessage());

        return 1;
    }

    $this->info('Records restored.');
    $this->info("Uploaded files put back: {$result['files_restored']}");

    foreach ($result['files_failed'] as $entry) {
        $this->warn("Could not put back: {$entry}");
    }

    $this->info('Done. Sign in with an account from the restored data.');

    return 0;
})->purpose('Restore every record and uploaded file from an EstateFlow backup zip');
