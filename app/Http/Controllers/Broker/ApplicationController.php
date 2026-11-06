<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Document;
use App\Services\BrokerComplianceService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ApplicationController extends Controller
{
    public function __construct(private BrokerComplianceService $compliance)
    {
    }

    public function show(): View
    {
        $broker = auth()->user();
        $checklist = $this->compliance->checklist($broker);
        $verifiedCount = collect($checklist)->where('document.status', 'verified')->count();
        $rejection = AppNotification::where('user_id', $broker->id)->where('type', 'broker_rejected')->latest()->first();

        return view('pages.broker.application', compact('broker', 'checklist', 'verifiedCount', 'rejection'));
    }

    public function updateDetails(Request $request): RedirectResponse
    {
        $broker = auth()->user();

        $data = $request->validate(
            $this->compliance->detailRules($broker->id),
            $this->compliance->messages(),
            $this->compliance->attributes()
        );

        $broker->update($data);

        return back()->with('success', 'Your details have been updated.');
    }

    public function storeDocument(Request $request): RedirectResponse
    {
        $broker = auth()->user();

        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(Document::BROKER_COMPLIANCE_TYPES))],
            'file' => BrokerComplianceService::FILE_RULES,
        ], $this->compliance->messages());

        $existing = $broker->complianceDocuments()->where('type', $data['type'])->latest()->first();

        if ($existing?->status === 'verified') {
            return back()->withErrors(['file' => 'This document is already verified and cannot be replaced.']);
        }

        $this->compliance->storeDocument($broker, $data['type'], $request->file('file'));
        $this->compliance->notifyAdminsWhenReadyForReview($broker);

        return back()->with('success', Document::BROKER_COMPLIANCE_TYPES[$data['type']]['label'].' uploaded.');
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
}
