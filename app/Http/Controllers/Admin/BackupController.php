<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BackupService;
use App\Services\InvalidBackupException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class BackupController extends Controller
{
    public function __construct(private BackupService $backups)
    {
    }

    public function index(): View
    {
        return view('pages.admin.backups.index', ['backups' => $this->backups->all()]);
    }

    public function store(): RedirectResponse
    {
        try {
            $this->backups->create();
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['backup' => 'The backup could not be created. Please try again.']);
        }

        return back()->with('success', 'Backup created. Download it and keep a copy somewhere safe.');
    }

    public function download(Request $request, string $backup): StreamedResponse
    {
        $this->confirmPassword($request);
        abort_unless($this->backups->exists($backup), 404);

        return Storage::disk('local')->download($this->backups->path($backup), $backup);
    }

    public function downloadSql(Request $request, string $backup): StreamedResponse|RedirectResponse
    {
        $this->confirmPassword($request);
        abort_unless($this->backups->exists($backup), 404);

        if (! $this->backups->hasSql($backup)) {
            return back()->withErrors(['backup' => 'This backup has no database file to download.']);
        }

        return response()->streamDownload(
            fn () => $this->backups->streamSql($backup),
            preg_replace('/\.zip$/', '.sql', $backup),
            ['Content-Type' => 'application/sql']
        );
    }

    /**
     * Replaces all records with an uploaded EstateFlow .sql backup, after saving the current data first.
     */
    public function import(Request $request): RedirectResponse
    {
        $this->confirmPassword($request);
        $request->validate([
            'backup_file' => ['required', 'file', 'extensions:sql,zip', 'max:204800'],
            'confirm_replace' => ['accepted'],
        ], [
            'backup_file.required' => 'Choose the backup file to import.',
            'backup_file.extensions' => 'Choose a .zip or .sql file downloaded from this Backups page.',
            'backup_file.max' => 'The file is larger than 200 MB. Use the php artisan backup:restore command instead.',
            'confirm_replace' => 'Tick the box to confirm that the current data will be replaced.',
        ]);

        $upload = $request->file('backup_file');
        $isZip = strtolower($upload->getClientOriginalExtension()) === 'zip';

        try {
            // A safety copy first, so this import can always be undone.
            $safetyBackup = $this->backups->create();
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['backup' => 'A safety backup of the current data could not be made, so nothing was imported.']);
        }

        try {
            $result = $isZip
                ? $this->backups->restoreArchive($upload->getRealPath())
                : ($this->backups->restore((string) file_get_contents($upload->getRealPath())) ?? ['files_restored' => 0, 'files_failed' => []]);
        } catch (InvalidBackupException $e) {
            $this->backups->delete($safetyBackup);

            return back()->withErrors(['backup' => $e->getMessage()]);
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors(['backup' => 'The backup could not be imported, so nothing was changed. The file may come from an older or newer version of EstateFlow.']);
        }

        // Accounts now come from the imported data, where this id may belong to someone else, so sign in again.
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $files = $isZip ? " {$result['files_restored']} uploaded files were put back." : '';
        $failed = $result['files_failed'] ? ' '.count($result['files_failed']).' files could not be put back; see storage/logs/laravel.log.' : '';

        return redirect()->route('auth.login')->with('success', "Backup imported.{$files}{$failed} Please sign in with an account from the imported data. The data from before the import was saved as {$safetyBackup}.");
    }

    public function destroy(Request $request, string $backup): RedirectResponse
    {
        $this->confirmPassword($request);
        abort_unless($this->backups->exists($backup), 404);

        $this->backups->delete($backup);

        return back()->with('success', 'Backup deleted.');
    }

    private function confirmPassword(Request $request): void
    {
        $request->validate(
            ['password' => ['required', 'current_password']],
            ['password.current_password' => 'That password is incorrect.', 'password.required' => 'Please enter your password.']
        );
    }
}
