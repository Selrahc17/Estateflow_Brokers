<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Lot;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReservationController extends Controller
{
    /**
     * What an admin can do from each status, with what happens to the lot.
     * A completed sale is final, so it offers nothing.
     */
    public const ACTIONS = [
        'requested' => ['confirmed', 'cancelled'],
        'pending' => ['confirmed', 'completed', 'cancelled'],
        'confirmed' => ['completed', 'cancelled'],
        'cancelled' => ['pending'],
        'completed' => [],
    ];

    public function index(Request $request): View
    {
        $tabs = ['all' => 'All'] + Reservation::STATUSES;
        $status = array_key_exists((string) $request->input('status'), $tabs) ? $request->input('status') : 'all';
        $search = trim((string) $request->input('search'));
        $brokers = User::where('role', 'broker')->orderBy('name')->get(['id', 'name']);
        $brokerId = $brokers->contains('id', (int) $request->input('broker')) ? (int) $request->input('broker') : null;

        $scope = fn () => Reservation::query()
            ->when($brokerId, fn ($query) => $query->whereHas('broker', fn ($handler) => $handler
                ->where('broker_id', $brokerId)->orWhere('users.id', $brokerId)))
            ->when($search !== '', function ($query) use ($search) {
                $like = "%{$search}%";
                $query->where(fn ($match) => $match->where('reservation_code', 'like', $like)
                    ->orWhereHas('client', fn ($client) => $client->where('first_name', 'like', $like)
                        ->orWhere('last_name', 'like', $like)
                        ->orWhere('email', 'like', $like))
                    ->orWhereHas('lot.property', fn ($property) => $property->where('name', 'like', $like))
                    ->orWhereHas('broker', fn ($agent) => $agent->where('name', 'like', $like)));
            });

        $counts = $scope()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');
        $counts['all'] = $counts->sum();

        $reservations = $scope()
            ->with(['client', 'broker.broker', 'lot.property'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.admin.reservations.index', compact('reservations', 'tabs', 'status', 'counts', 'search', 'brokers', 'brokerId'));
    }

    public function show(Reservation $reservation): View
    {
        $reservation->load(['client', 'broker.broker', 'lot.property']);

        return view('pages.admin.reservations.show', [
            'reservation' => $reservation,
            'actions' => self::ACTIONS[$reservation->status] ?? [],
        ]);
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

            $wasHolding = $reservation->holdsLot();
            $willHold = in_array($status, Reservation::HOLDING, true);

            // A cancelled reservation or a buyer's request takes the lot only if it is still free.
            if ($lot && ! $wasHolding && ($willHold || $status === 'completed') && $lot->status !== 'available') {
                return $reservation->status === 'cancelled'
                    ? 'This lot has been taken since the reservation was cancelled.'
                    : "{$lot->lot_number} is already reserved for someone else.";
            }

            $reservation->update(['status' => $status]);

            if ($lot) {
                if ($status === 'completed') {
                    $lot->update(['status' => 'sold']);
                } elseif ($willHold) {
                    $lot->update(['status' => 'reserved']);
                } elseif ($status === 'cancelled' && $wasHolding && $lot->status === 'reserved') {
                    // Only the reservation that held the lot gives it back.
                    $lot->update(['status' => 'available']);
                }
            }

            return null;
        });

        if ($problem) {
            return back()->withErrors(['status' => $problem]);
        }

        $this->notifyParties($reservation, $status);

        $code = $reservation->reservation_code;

        return back()->with('success', match ($status) {
            'confirmed' => "Reservation {$code} confirmed. The lot is now reserved.",
            'completed' => "Reservation {$code} completed. The lot is marked as sold.",
            'cancelled' => "Reservation {$code} cancelled.",
            default => "Reservation {$code} is pending again.",
        });
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
