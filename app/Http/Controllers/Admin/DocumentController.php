<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(): View
    {
        $documents = Document::with('client', 'uploader')->latest()->paginate(15);
        return view('pages.admin.documents.index', compact('documents'));
    }

    public function verify(Document $document): RedirectResponse
    {
        $document->update(['status' => 'verified', 'verified_at' => now(), 'uploaded_by' => auth()->id()]);
        return back()->with('success', 'Document verified.');
    }

    public function reject(Request $request, Document $document): RedirectResponse
    {
        $document->update(['status' => 'rejected', 'notes' => $request->notes, 'uploaded_by' => auth()->id()]);
        return back()->with('success', 'Document rejected.');
    }
}