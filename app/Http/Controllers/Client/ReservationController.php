<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Lot;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

        // Each agent reviews the buyer separately, so only this listing's agent's approval counts.
        $client = auth()->user()->clientProfiles()->where('broker_id', $lot->property?->broker_id)->first();
        if (!$client) {
            return back()->withErrors([
                'reservation' => 'Send an inquiry for this property first. Its agent needs to review your requirements before you can reserve.',
            ]);
        }

        if ($client->qualification_status !== 'approved') {
            return back()->withErrors([
                'reservation' => 'Your requirements must be approved before requesting a reservation.',
            ]);
        }

        $reservation = DB::transaction(function () use ($lot, $client, $request) {
            $lot = Lot::with('property')
                ->whereKey($lot->id)
                ->where('status', 'available')
                ->lockForUpdate()
                ->first();

            if (!$lot || !$lot->property || $lot->property->status !== 'available') {
                return null;
            }

            $reservation = Reservation::create([
                'client_id' => $client->id,
                'agent_id' => $client->agent_id,
                'lot_id' => $lot->id,
                'broker_id' => $lot->property->broker_id,
                'reservation_code' => 'RES-' . strtoupper(Str::random(8)),
                'status' => 'pending',
                'total_price' => $lot->price ?? $lot->property->price,
                'notes' => $request->input('notes'),
                'reserved_at' => now(),
                'expires_at' => now()->addDays(30),
            ]);

            $lot->update(['status' => 'reserved']);

            return $reservation;
        });

        if (!$reservation) {
            return back()->withErrors([
                'reservation' => 'This lot is no longer available for reservation.',
            ]);
        }

        return back()->with('success', 'Reservation request submitted successfully.');
    }
}