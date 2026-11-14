<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Document;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DocumentController extends Controller
{
    public function index(): View
    {
        // Broker and agent documents only. Buyer requirements are handled by the
        // developer's coordinator, so older buyer uploads stay stored but hidden.
        $documents = Document::whereNull('client_id')->with('broker', 'uploader')->latest()->paginate(15);
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
        $path = env('SUPABASE_URL') . '/storage/v1/object/public/properties/' . $filename;
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

    public function download(Document $document)
    {
        if (str_starts_with($document->file_path, 'http')) {
            return redirect()->away($document->file_path);
        }

        $filename = $document->name.'.'.pathinfo($document->file_path, PATHINFO_EXTENSION);

        foreach (['local', 'public'] as $disk) {
            if (Storage::disk($disk)->exists($document->file_path)) {
                return Storage::disk($disk)->download($document->file_path, $filename);
            }
        }

        abort(404);
    }

    public function verify(Document $document): RedirectResponse
    {
        $document->update(['status' => 'verified', 'verified_at' => now(), 'notes' => null]);
        $this->notifyOwner($document, "{$document->name} was verified.");

        return back()->with('success', 'Document verified.');
    }

    public function reject(Request $request, Document $document): RedirectResponse
    {
        $notes = $this->reason($request);
        $document->update(['status' => 'rejected', 'notes' => $notes, 'verified_at' => null]);
        $this->notifyOwner($document, "{$document->name} was rejected: {$notes}");

        return back()->with('success', 'Document rejected.');
    }

    public function requestMore(Request $request, Document $document): RedirectResponse
    {
        $notes = $this->reason($request);
        $document->update(['status' => 'needs_more', 'notes' => $notes, 'verified_at' => null]);
        $this->notifyOwner($document, "More is needed for {$document->name}: {$notes}");

        return back()->with('success', 'Requested additional documents.');
    }

    private function reason(Request $request): string
    {
        return $request->validate(
            ['notes' => ['required', 'string', 'max:1000']],
            ['notes.required' => 'Please give a reason so the owner knows what to fix.']
        )['notes'];
    }

    private function notifyOwner(Document $document, string $message): void
    {
        // Only broker documents are reviewed here; buyers no longer submit documents.
        if ($document->broker_id && ! $document->client_id) {
            AppNotification::create([
                'user_id' => $document->broker_id,
                'type' => 'document_review',
                'title' => 'Document review update',
                'message' => $message,
                'is_read' => false,
            ]);
        }
    }

    public function destroy(Document $document): RedirectResponse
    {
        $document->delete();
        return back()->with('success', 'Document deleted.');
    }
}
