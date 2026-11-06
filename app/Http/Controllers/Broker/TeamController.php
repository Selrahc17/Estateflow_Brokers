<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Inquiry;
use App\Models\Reservation;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Read-only view of the work done by a broker's agents. On these records
 * broker_id holds the agent's user id, so the scope is the broker's agents.
 */
class TeamController extends Controller
{
    public function clients(): View
    {
        $agents = $this->agents();

        $clients = Client::whereIn('broker_id', $this->scopedAgentIds($agents))
            ->when(request('search'), function ($query) {
                $search = '%'.request('search').'%';
                $query->where(fn ($q) => $q->where('first_name', 'like', $search)
                    ->orWhere('last_name', 'like', $search)
                    ->orWhere('email', 'like', $search));
            })
            ->when(request('status'), fn ($query) => $query->where('qualification_status', request('status')))
            ->with('broker:id,name')
            ->withCount('reservations')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.broker.team.clients', compact('clients', 'agents'));
    }

    public function showClient(Client $client): View
    {
        $agents = $this->agents();
        abort_unless($agents->contains('id', $client->broker_id), 404);

        $client->load([
            'broker:id,name',
            'reservations' => fn ($query) => $query->whereIn('broker_id', $agents->pluck('id'))->with('lot.property')->latest(),
            'siteVisits' => fn ($query) => $query->whereIn('broker_id', $agents->pluck('id'))->with('property')->latest(),
        ]);

        $inquiries = Inquiry::whereIn('broker_id', $agents->pluck('id'))
            ->where('email', $client->email)
            ->with('property')
            ->latest()
            ->get();

        return view('pages.broker.team.client', compact('client', 'inquiries'));
    }

    public function inquiries(): View
    {
        $agents = $this->agents();

        $inquiries = Inquiry::whereIn('broker_id', $this->scopedAgentIds($agents))
            ->when(request('search'), function ($query) {
                $search = '%'.request('search').'%';
                $query->where(fn ($q) => $q->where('email', 'like', $search)
                    ->orWhere('message', 'like', $search)
                    ->orWhereHas('property', fn ($property) => $property->where('name', 'like', $search)));
            })
            ->when(request('status'), fn ($query) => $query->where('status', request('status')))
            ->with(['broker:id,name', 'property:id,name'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.broker.team.inquiries', compact('inquiries', 'agents'));
    }

    public function reservations(): View
    {
        $agents = $this->agents();

        $reservations = Reservation::whereIn('broker_id', $this->scopedAgentIds($agents))
            ->when(request('search'), function ($query) {
                $search = '%'.request('search').'%';
                $query->where(fn ($q) => $q->where('reservation_code', 'like', $search)
                    ->orWhereHas('client', fn ($client) => $client->where('first_name', 'like', $search)
                        ->orWhere('last_name', 'like', $search)
                        ->orWhere('email', 'like', $search)));
            })
            ->when(request('status'), fn ($query) => $query->where('status', request('status')))
            ->with(['broker:id,name', 'client', 'lot.property'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.broker.team.reservations', compact('reservations', 'agents'));
    }

    private function agents(): Collection
    {
        return auth()->user()->agents()->orderBy('name')->get(['id', 'name']);
    }

    /**
     * The selected agent, or the whole team. An id outside the team matches nothing.
     */
    private function scopedAgentIds(Collection $agents): Collection
    {
        $ids = $agents->pluck('id');

        return request('agent') ? $ids->intersect([(int) request('agent')]) : $ids;
    }
}
