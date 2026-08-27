<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use App\Services\AIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function __construct(private AIService $ai) {}

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
            ->where('broker_id', $brokerId)
            ->when(request('search'), function ($query) {
                $search = request('search');
                $query->where(function ($searchQuery) use ($search) {
                    $searchQuery->where('first_name', 'like', '%'.$search.'%')
                        ->orWhere('last_name', 'like', '%'.$search.'%')
                        ->orWhere('email', 'like', '%'.$search.'%');
                });
            })
            ->withCount('reservations')
            ->latest()
            ->paginate(15);

        return view('pages.clients.index', compact('clients'));
    }

    public function show(Client $client): View
    {
        $this->ensureOwnership($client);
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

        return redirect()->route('agent.clients.index')->with('success', 'Client added successfully.');
    }

    public function edit(Client $client): View
    {
        $this->ensureOwnership($client);
        return view('pages.clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client): RedirectResponse
    {
        $this->ensureOwnership($client);
        $data = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name'  => 'required|string|max:255',
            'email'      => 'required|email|unique:clients,email,' . $client->id,
            'phone'      => 'nullable|string|max:20',
            'address'    => 'nullable|string',
            'status'     => 'required|in:active,inactive,suspended',
        ]);

        $client->update($data);

        return redirect()->route('agent.clients.index')->with('success', 'Client updated successfully.');
    }

    public function aiLeadScore(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_id' => ['required', Rule::exists('clients', 'id')->where('broker_id', auth()->id())],
        ]);

        $client = Client::findOrFail($data['client_id']);
        $lastInquiry = \App\Models\Inquiry::where('broker_id', auth()->id())
            ->where('email', $client->email)
            ->latest()
            ->first();

        $lead = [
            'client_id' => $client->id,
            'name' => $client->full_name,
            'inquiry_count' => \App\Models\Inquiry::where('broker_id', auth()->id())
                ->where('email', $client->email)
                ->count(),
            'has_reservation' => \App\Models\Reservation::where('broker_id', auth()->id())
                ->where('client_id', $client->id)
                ->exists(),
            'has_site_visit' => \App\Models\SiteVisit::where('broker_id', auth()->id())
                ->where('client_id', $client->id)
                ->exists(),
            'days_since_last_contact' => $lastInquiry ? $lastInquiry->created_at->diffInDays(now()) : 99,
        ];

        return response()->json($this->ai->scoreLeads([$lead])[0]);
    }

    private function ensureOwnership(Client $client): void
    {
        abort_unless((int) $client->broker_id === (int) auth()->id(), 403);
    }
}