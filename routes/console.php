<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
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
