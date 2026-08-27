<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Lot;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ReservationController extends Controller
{
    public function index(): View
    {
        $reservations = Reservation::where('broker_id', auth()->id())
            ->with(['client', 'lot.property'])
            ->latest()
            ->paginate(15);

        return view('pages.reservations.index', compact('reservations'));
    }

    public function create(): View
    {
        $clients = Client::where('broker_id', auth()->id())->get();
        $lots = Lot::whereHas('property', fn($q) => $q->where('broker_id', auth()->id()))
            ->where('status', 'available')
            ->with('property')
            ->get();

        return view('pages.reservations.create', compact('clients', 'lots'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_id'          => ['required', Rule::exists('clients', 'id')->where('broker_id', auth()->id())],
            'lot_id'             => [
                'required',
                Rule::exists('lots', 'id')->whereIn(
                    'property_id',
                    \App\Models\Property::where('broker_id', auth()->id())->select('id')
                ),
            ],
            'total_price'        => 'required|numeric|min:0',
            'down_payment'       => 'nullable|numeric|min:0',
            'payment_schedule'   => 'required|in:monthly,quarterly,annual',
            'payment_terms_months' => 'required|integer|min:1|max:360',
            'notes'              => 'nullable|string',
        ]);

        $data['broker_id'] = auth()->id();
        $data['reservation_code'] = 'RES-' . strtoupper(Str::random(8));
        $data['status'] = 'pending';
        $data['reserved_at'] = now();
        $data['expires_at'] = now()->addDays(30);

        $reservation = DB::transaction(function () use ($data) {
            $lot = Lot::whereKey($data['lot_id'])
                ->where('status', 'available')
                ->lockForUpdate()
                ->first();

            if (!$lot) {
                return null;
            }

            $reservation = Reservation::create($data);
            $lot->update(['status' => 'reserved']);

            return $reservation;
        });

        if (!$reservation) {
            return back()->withErrors([
                'lot_id' => 'This lot is no longer available for reservation.',
            ])->withInput();
        }

        return redirect()->route('agent.reservations.index')
            ->with('success', "Reservation {$reservation->reservation_code} created successfully.");
    }

    public function show(Reservation $reservation): View
    {
        $this->ensureOwnership($reservation);
        $reservation->load(['client', 'lot.property']);
        return view('pages.reservations.show', compact('reservation'));
    }

    public function updateStatus(Request $request, Reservation $reservation): RedirectResponse
    {
        $this->ensureOwnership($reservation);
        $data = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
        ]);

        $reservation->update($data);

        // Update lot status accordingly
        if ($reservation->lot) {
            $lotStatus = match ($data['status']) {
                'cancelled' => 'available',
                'completed' => 'sold',
                default => 'reserved',
            };

            $reservation->lot->update(['status' => $lotStatus]);
        }

        return redirect()->route('agent.reservations.show', $reservation)
            ->with('success', 'Reservation status updated successfully.');
    }

    private function ensureOwnership(Reservation $reservation): void
    {
        abort_unless((int) $reservation->broker_id === (int) auth()->id(), 403);
    }
}