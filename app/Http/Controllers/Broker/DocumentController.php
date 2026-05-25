<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
        $document->update([
            'status' => 'verified',
            'verified_at' => now(),
            'uploaded_by' => auth()->id(),
        ]);

        return redirect()->route('broker.documents.index')->with('success', 'Document verified successfully.');
    }

    public function reject(Request $request, Document $document): RedirectResponse
    {
        $request->validate(['notes' => 'nullable|string']);
        $document->update([
            'status' => 'rejected',
            'notes' => $request->notes,
            'uploaded_by' => auth()->id(),
        ]);

        return redirect()->route('broker.documents.index')->with('success', 'Document rejected.');
    }
}