<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Lot;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function index(): View
    {
        $reservations = Reservation::with(['client', 'broker', 'lot.property'])
            ->when(request('status'), fn($q) => $q->where('status', request('status')))
            ->when(request('search'), function ($q) {
                $search = '%'.request('search').'%';
                $q->where(fn ($query) => $query->where('reservation_code', 'like', $search)
                    ->orWhereHas('client', fn ($client) => $client->where('first_name', 'like', $search)
                        ->orWhere('last_name', 'like', $search)
                        ->orWhere('email', 'like', $search)));
            })
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.admin.reservations.index', compact('reservations'));
    }

    public function show(Reservation $reservation): View
    {
        $reservation->load(['client', 'broker', 'lot.property', 'payments']);
        return view('pages.admin.reservations.show', compact('reservation'));
    }

    public function updateStatus(Request $request, Reservation $reservation): RedirectResponse
    {
        $request->validate(['status' => 'required|in:pending,confirmed,cancelled,completed']);

        $status = $request->status;

        if ($status === $reservation->status) {
            return back();
        }

        $problem = DB::transaction(function () use ($reservation, $status) {
            $lot = $reservation->lot ? Lot::whereKey($reservation->lot_id)->lockForUpdate()->first() : null;

            if ($reservation->status === 'completed') {
                return 'A completed sale cannot be changed.';
            }

            if ($reservation->status === 'cancelled' && $lot && $lot->status !== 'available') {
                return 'This lot has been taken since the reservation was cancelled.';
            }

            $reservation->update(['status' => $status]);
            $lot?->update(['status' => match ($status) {
                'cancelled' => 'available',
                'completed' => 'sold',
                default => 'reserved',
            }]);

            return null;
        });

        if ($problem) {
            return back()->withErrors(['status' => $problem]);
        }

        $this->notifyParties($reservation, $status);

        return back()->with('success', 'Reservation updated.');
    }

    private function notifyParties(Reservation $reservation, string $status): void
    {
        $reservation->loadMissing('client');

        collect([$reservation->broker_id, $reservation->client?->user_id])
            ->filter()
            ->unique()
            ->each(fn (int $userId) => AppNotification::create([
                'user_id' => $userId,
                'type' => 'reservation_status',
                'title' => 'Reservation '.$status,
                'message' => "Reservation {$reservation->reservation_code} is now {$status}.",
                'is_read' => false,
            ]));
    }
}
