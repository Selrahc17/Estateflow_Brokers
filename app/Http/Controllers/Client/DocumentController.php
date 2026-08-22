<?php

namespace App\Http\Controllers\Client;

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
        $client = auth()->user()->clientProfile;
        $documents = $client
            ? Document::where('client_id', $client->id)->latest()->paginate(10)
            : collect();

        return view('pages.client.documents.index', compact('documents'));
    }

    public function store(Request $request): RedirectResponse
    {
        $client = auth()->user()->clientProfile;

        if (!$client) {
            return back()->withErrors([
                'document' => 'A client profile is required before uploading documents.',
            ]);
        }

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:contract,id,proof_of_income,receipt,other',
            'file' => 'required|file|max:10240',
        ]);

        $data['file_path'] = $request->file('file')->store('documents/' . $client->id, 'local');
        $data['file_size'] = $request->file('file')->getSize();
        $data['client_id'] = $client->id;
        $data['status'] = 'pending';

        Document::create($data);

        return redirect()->route('client.account.documents')->with('success', 'Document uploaded successfully.');
    }

    public function download(Document $document)
    {
        abort_unless($document->client_id === auth()->user()->clientProfile?->id, 403);

        if (Storage::disk('local')->exists($document->file_path)) {
            return Storage::disk('local')->download($document->file_path, $document->name);
        }

        abort_unless(Storage::disk('public')->exists($document->file_path), 404);

        return Storage::disk('public')->download($document->file_path, $document->name);
    }
}