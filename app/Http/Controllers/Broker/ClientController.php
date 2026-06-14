<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(): View
    {
        $clients = Client::where('broker_id', auth()->id())
            ->withCount('reservations')
            ->latest()
            ->paginate(15);

        return view('pages.clients.index', compact('clients'));
    }

    public function show(Client $client): View
    {
        $client->load(['reservations' => fn($q) => $q->with('lot.property')]);
        return view('pages.clients.show', compact('client'));
    }

    public function create(): View
    {
        return view('pages.clients.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:clients,email',
            'phone'      => 'nullable|string|max:20',
            'address'    => 'nullable|string',
        ]);

        $data['broker_id'] = auth()->id();
        $data['status'] = 'active';

        Client::create($data);

        return redirect()->route('broker.clients.index')->with('success', 'Client added successfully.');
    }

    public function edit(Client $client): View
    {
        return view('pages.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:clients,email,' . $client->id,
            'phone'      => 'nullable|string|max:20',
            'address'    => 'nullable|string',
            'status'     => 'required|in:active,inactive,suspended',
        ]);

        $client->update($data);

        return redirect()->route('broker.clients.index')->with('success', 'Client updated successfully.');
    }
}