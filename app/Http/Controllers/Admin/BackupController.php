<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\BackupService;
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
