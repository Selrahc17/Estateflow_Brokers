<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\User;
use App\Services\AIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function __construct(private AIService $ai) {}

    /**
     * Every client this agent has, including ones added by hand that have no
     * inquiry, visit or reservation yet.
     */
    public function index(): View
    {
        $search = trim((string) request('search'));
        $stage = in_array(request('stage'), ['pending', 'under_review', 'approved', 'rejected'], true) ? request('stage') : null;
        $stageCounts = Client::where('broker_id', auth()->id())
            ->selectRaw('qualification_status, count(*) as total')->groupBy('qualification_status')->pluck('total', 'qualification_status');

        $clients = Client::where('broker_id', auth()->id())
            ->when($stage, fn ($query) => $query->where('qualification_status', $stage))
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . mb_strtolower($search) . '%';
                $query->where(fn ($match) => $match
                    ->whereRaw('lower(first_name) like ?', [$like])
                    ->orWhereRaw('lower(last_name) like ?', [$like])
                    ->orWhereRaw("lower(first_name || ' ' || last_name) like ?", [$like])
                    ->orWhereRaw('lower(email) like ?', [$like])
                    ->orWhereRaw('lower(phone) like ?', [$like]));
            })
            ->withCount(['reservations', 'siteVisits'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.clients.index', compact('clients', 'stage', 'stageCounts', 'search'));
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
            'email'      => ['required', 'email', Rule::unique('clients', 'email')->where('broker_id', auth()->id())],
            'phone'      => 'nullable|string|max:20',
            'address'    => 'nullable|string',
        ]);

        $data['broker_id'] = auth()->id();
        $data['status'] = 'active';
        // Clients sign in through their own account, so this record only needs an unusable password.
        $data['password'] = Hash::make(Str::random(24));

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
            'email'      => ['required', 'email', Rule::unique('clients', 'email')->where('broker_id', auth()->id())->ignore($client->id)],
            'phone'      => 'nullable|string|max:20',
            'address'    => 'nullable|string',
            'status'     => 'required|in:active,inactive,suspended',
        ]);

        $client->update($data);

        return redirect()->route('agent.clients.index')->with('success', 'Client updated successfully.');
    }

    public function updateQualificationStatus(Request $request, Client $client): RedirectResponse
    {
        $this->ensureOwnership($client);

        $data = $request->validate([
            'qualification_status' => 'required|in:pending,under_review,approved,rejected',
        ]);

        // The status is a label for the agent's own tracking; it no longer blocks anything.
        $client->update($data);

        return back()->with('success', 'Client status updated.');
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