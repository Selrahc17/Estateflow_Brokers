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
        $brokerId = auth()->id();

        $clientIds = collect();

        $clientIds = $clientIds->merge(
            \App\Models\Inquiry::where('broker_id', $brokerId)
                ->select('email')
                ->distinct()
                ->pluck('email')
                ->filter()
                ->map(fn ($email) => Client::where('email', $email)->where('broker_id', $brokerId)->value('id'))
                ->filter()
        );

        $clientIds = $clientIds->merge(
            \App\Models\Reservation::where('broker_id', $brokerId)->pluck('client_id')
        );

        $clientIds = $clientIds->merge(
            \App\Models\SiteVisit::where('broker_id', $brokerId)->pluck('client_id')
        );

        $clientIds = $clientIds->merge(
            \App\Models\Payment::where('broker_id', $brokerId)->pluck('client_id')
        );

        $clientIds = $clientIds->merge(
            \App\Models\Feedback::where('user_id', $brokerId)->pluck('client_id')
        );

        $clientIds = $clientIds->unique()->filter();

        $clients = Client::whereIn('id', $clientIds)
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