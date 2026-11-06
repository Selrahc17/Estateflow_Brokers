<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\BrokerComplianceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(BrokerComplianceService $compliance): View
    {
        return view('pages.broker.settings.index', [
            'checklist' => $compliance->checklist(auth()->user()),
        ]);
    }

    public function updateLicense(Request $request, BrokerComplianceService $compliance): RedirectResponse
    {
        $broker = auth()->user();

        $broker->update($request->validate(
            $compliance->licenseRules($broker->id),
            $compliance->messages(),
            $compliance->attributes()
        ));

        return back()->with('success', 'License details updated.');
    }

    public function storeDocument(Request $request, BrokerComplianceService $compliance): RedirectResponse
    {
        $broker = auth()->user();

        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(Document::BROKER_COMPLIANCE_TYPES))],
            'file' => BrokerComplianceService::FILE_RULES,
        ], $compliance->messages());

        $compliance->storeDocument($broker, $data['type'], $request->file('file'));
        $compliance->notifyAdminsOfRenewal($broker, $data['type']);

        return back()->with('success', Document::BROKER_COMPLIANCE_TYPES[$data['type']]['label'].' uploaded for admin review.');
    }

    public function downloadDocument(Document $document)
    {
        abort_unless(auth()->user()->complianceDocuments()->whereKey($document->id)->exists(), 404);
        abort_unless(Storage::disk('local')->exists($document->file_path), 404);

        return Storage::disk('local')->download(
            $document->file_path,
            $document->name.'.'.pathinfo($document->file_path, PATHINFO_EXTENSION)
        );
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . auth()->id(),
            'phone' => 'nullable|string|max:20',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $update = [
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
        ];

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'avatars/' . auth()->id() . '_' . time() . '.' . $file->getClientOriginalExtension();
            Storage::disk('supabase')->put($filename, file_get_contents($file));
            $update['avatar'] = env('SUPABASE_URL') . '/storage/v1/object/public/properties/' . $filename;
        }

        auth()->user()->update($update);

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password' => 'required|string|min:8|confirmed',
        ]);

        auth()->user()->update(['password' => $request->password]);

        return back()->with('success', 'Password updated successfully.');
    }
}
