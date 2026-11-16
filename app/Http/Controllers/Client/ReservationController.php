<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Lot;
use App\Models\Reservation;
use App\Notifications\NewReservationRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function index(): View
    {
        $reservations = Reservation::whereIn('client_id', auth()->user()->clientProfiles()->select('id'))
            ->with('lot.property')
            ->latest()
            ->paginate(10);

        return view('pages.client.reservation.index', compact('reservations'));
    }

    public function store(Request $request, Lot $lot): RedirectResponse
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000',
        ]);

        // Only the listing's agent works with this buyer, so their client record is the one that counts.
        $client = auth()->user()->clientProfiles()->where('broker_id', $lot->property?->broker_id)->first();
        if (!$client) {
            return back()->with('error', 'Send an inquiry for this property first. Once the agent adds you as their client, you can request a lot.');
        }

        $lot->loadMissing('property');
        if ($lot->status !== 'available' || ! $lot->property || $lot->property->status !== 'available') {
            return back()->with('error', "{$lot->lot_number} is no longer available.");
        }

        $alreadyAsked = Reservation::where('client_id', $client->id)->where('lot_id', $lot->id)
            ->whereIn('status', ['requested', ...Reservation::HOLDING])->exists();
        if ($alreadyAsked) {
            return back()->with('error', "You already asked for {$lot->lot_number}. The agent will confirm it with you.");
        }

        // A request does not hold the lot. The agent confirms it, and only then is the lot reserved.
        $reservation = Reservation::create([
            'client_id' => $client->id,
            'agent_id' => $lot->property->broker_id,
            'lot_id' => $lot->id,
            'broker_id' => $lot->property->broker_id,
            'reservation_code' => 'RES-' . strtoupper(Str::random(8)),
            'status' => 'requested',
            'total_price' => $lot->price ?? $lot->property->price,
            'notes' => $request->input('notes'),
            'reserved_at' => now(),
            'expires_at' => now()->addDays(30),
        ]);

        AppNotification::create([
            'user_id' => $lot->property->broker_id,
            'type' => 'reservation',
            'title' => 'New reservation request',
            'message' => auth()->user()->name . " asked to reserve {$lot->lot_number} at {$lot->property->name}.",
            'link' => route('agent.reservations.show', $reservation),
            'is_read' => false,
        ]);

        // Also email the agent, after the buyer's page has loaded.
        $this->emailAgent($lot->property->broker, new NewReservationRequest($reservation->load(['client', 'lot.property'])));

        $agent = $lot->property->broker?->name ?? 'The agent';

        return back()->with('success', "Reservation request sent for {$lot->lot_number}. {$agent} will confirm it with you.");
    }
}