<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:contract,id,proof_of_income,receipt,other',
            'file' => 'required|file|max:10240',
        ]);

        $data['file_path'] = $request->file('file')->store('documents/' . $client->id, 'public');
        $data['file_size'] = $request->file('file')->getSize();
        $data['client_id'] = $client->id;
        $data['status'] = 'pending';

        Document::create($data);

        return redirect()->route('client.account.documents')->with('success', 'Document uploaded successfully.');
    }
}