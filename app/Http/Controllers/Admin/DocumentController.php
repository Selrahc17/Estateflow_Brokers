<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(): View
    {
        $documents = Document::with('broker', 'client', 'uploader')->latest()->paginate(15);
        $brokers = User::where('role', 'broker')->orderBy('name')->get();
        return view('pages.admin.documents.index', compact('documents', 'brokers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'broker_id' => 'required|exists:users,id',
            'name'      => 'required|string|max:255',
            'type'      => 'required|string|max:30',
            'file'      => 'required|file|max:10240',
            'notes'     => 'nullable|string|max:1000',
        ]);

        $file = $request->file('file');
        $filename = 'broker-documents/' . uniqid() . '.' . $file->getClientOriginalExtension();
        \Illuminate\Support\Facades\Storage::disk('supabase')->put($filename, file_get_contents($file->getRealPath()), 'public');
        $path = 'https://yungpjrhvpjneanvyxnt.supabase.co/storage/v1/object/public/properties/' . $filename;
        $size = round($file->getSize() / 1024) . ' KB';

        Document::create([
            'broker_id'   => $request->broker_id,
            'uploaded_by' => auth()->id(),
            'name'        => $request->name,
            'type'        => $request->type,
            'file_path'   => $path,
            'file_size'   => $size,
            'notes'       => $request->notes,
            'status'      => 'verified',
        ]);

        return back()->with('success', 'Document uploaded successfully.');
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

    public function requestMore(Request $request, Document $document): RedirectResponse
    {
        $document->update(['status' => 'needs_more', 'notes' => $request->notes, 'uploaded_by' => auth()->id()]);
        return back()->with('success', 'Requested additional documents.');
    }

    public function destroy(Document $document): RedirectResponse
    {
        $document->delete();
        return back()->with('success', 'Document deleted.');
    }
}
