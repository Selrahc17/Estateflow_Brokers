<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(): View
    {
        $documents = Document::whereHas('client', fn($q) => $q->where('broker_id', auth()->id()))
            ->with('client')
            ->latest()
            ->paginate(15);

        return view('pages.documents.index', compact('documents'));
    }

    public function verify(Document $document): RedirectResponse
    {
        $this->ensureOwnership($document);
        $document->update([
            'status' => 'verified',
            'verified_at' => now(),
            'uploaded_by' => auth()->id(),
        ]);

        return redirect()->route('agent.documents.index')->with('success', 'Document verified successfully.');
    }

    public function reject(Request $request, Document $document): RedirectResponse
    {
        $this->ensureOwnership($document);
        $request->validate(['notes' => 'nullable|string']);
        $document->update([
            'status' => 'rejected',
            'notes' => $request->notes,
            'uploaded_by' => auth()->id(),
        ]);

        return redirect()->route('agent.documents.index')->with('success', 'Document rejected.');
    }

    public function download(Document $document)
    {
        $this->ensureOwnership($document);

        if (Storage::disk('local')->exists($document->file_path)) {
            return Storage::disk('local')->download($document->file_path, $document->name);
        }

        abort_unless(Storage::disk('public')->exists($document->file_path), 404);

        return Storage::disk('public')->download($document->file_path, $document->name);
    }

    private function ensureOwnership(Document $document): void
    {
        abort_unless($document->client?->broker_id === auth()->id(), 403);
    }
}