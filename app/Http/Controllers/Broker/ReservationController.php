<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\Lot;
use App\Models\Reservation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
            'client_id'          => 'required|exists:clients,id',
            'lot_id'             => 'required|exists:lots,id',
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

        $reservation = Reservation::create($data);

        // Update lot status
        $reservation->lot->update(['status' => 'reserved']);

        return redirect()->route('broker.reservations.index')
            ->with('success', "Reservation {$reservation->reservation_code} created successfully.");
    }

    public function show(Reservation $reservation): View
    {
        $reservation->load(['client', 'lot.property']);
        return view('pages.reservations.show', compact('reservation'));
    }

    public function updateStatus(Request $request, Reservation $reservation): RedirectResponse
    {
        $data = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
        ]);

        $oldStatus = $reservation->status;
        $reservation->update($data);

        // Update lot status accordingly
        if ($data['status'] === 'cancelled') {
            $reservation->lot->update(['status' => 'available']);
        } elseif ($data['status'] === 'confirmed') {
            $reservation->lot->update(['status' => 'reserved']);
        } elseif ($data['status'] === 'completed') {
            $reservation->lot->update(['status' => 'sold']);
        }

        return redirect()->route('broker.reservations.show', $reservation)
            ->with('success', 'Reservation status updated successfully.');
    }
}