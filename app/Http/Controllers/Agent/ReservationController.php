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
    public function index(Request $request): View
    {
        $status = array_key_exists((string) $request->input('status'), Reservation::STATUSES) ? $request->input('status') : null;
        $search = trim((string) $request->input('search'));

        $counts = Reservation::where('broker_id', auth()->id())
            ->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status');

        $reservations = Reservation::where('broker_id', auth()->id())
            ->with(['client', 'lot.property'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . mb_strtolower($search) . '%';
                $query->where(fn ($match) => $match
                    ->whereRaw('lower(reservation_code) like ?', [$like])
                    ->orWhereHas('client', fn ($client) => $client->whereRaw("lower(first_name || ' ' || last_name) like ?", [$like])->orWhereRaw('lower(email) like ?', [$like]))
                    ->orWhereHas('lot', fn ($lot) => $lot->whereRaw('lower(lot_number) like ?', [$like])
                        ->orWhereHas('property', fn ($property) => $property->whereRaw('lower(name) like ?', [$like]))));
            })
            // Buyer requests waiting for the agent come first.
            ->orderByRaw("case when status = 'requested' then 0 else 1 end")
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.reservations.index', compact('reservations', 'counts', 'status', 'search'));
    }

    /**
     * Any of the agent's clients can be reserved for; the agent decides.
     * Available lots are grouped by project and in lot-number order.
     */
    public function create(): View
    {
        $clients = Client::where('broker_id', auth()->id())->orderBy('first_name')->orderBy('last_name')->get();
        $lotsByProject = Lot::whereHas('property', fn ($q) => $q->where('broker_id', auth()->id()))
            ->where('status', 'available')
            ->with('property:id,name,price')
            ->get()
            ->sort(fn (Lot $a, Lot $b) => [$a->property?->name, (int) ($a->block ?? PHP_INT_MAX), (int) $a->short_number, $a->lot_number]
                <=> [$b->property?->name, (int) ($b->block ?? PHP_INT_MAX), (int) $b->short_number, $b->lot_number])
            ->groupBy(fn (Lot $lot) => $lot->property?->name ?? 'Other');

        return view('pages.reservations.create', compact('clients', 'lotsByProject'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_id'          => ['required', Rule::exists('clients', 'id')->where('broker_id', auth()->id())->whereNull('deleted_at')],
            'lot_id'             => [
                'required',
                Rule::exists('lots', 'id')->whereIn(
                    'property_id',
                    \App\Models\Property::where('broker_id', auth()->id())->select('id')
                ),
            ],
            'total_price'        => 'required|numeric|min:0',
            // Financing is the developer's; only the reservation fee is recorded here.
            'reservation_fee'    => 'nullable|numeric|min:0|lte:total_price',
            'notes'              => 'nullable|string',
        ]);

        $data['broker_id'] = auth()->id();
        $data['agent_id'] = auth()->id();
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
        $status = $request->validate([
            'status' => 'required|in:pending,confirmed,cancelled,completed',
        ])['status'];

        $wasHolding = $reservation->holdsLot();
        $willHold = in_array($status, Reservation::HOLDING, true);

        $error = DB::transaction(function () use ($reservation, $status, $wasHolding, $willHold) {
            $lot = $reservation->lot_id ? Lot::whereKey($reservation->lot_id)->lockForUpdate()->first() : null;

            // A buyer's request takes the lot only now, and only if it is still free.
            if ($lot && ! $wasHolding && ($willHold || $status === 'completed') && $lot->status !== 'available') {
                return "{$lot->lot_number} is already " . ($lot->status === 'sold' ? 'sold' : 'reserved for someone else') . '.';
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

        if ($error) {
            return back()->withErrors(['status' => $error]);
        }

        return redirect()->route('agent.reservations.show', $reservation)
            ->with('success', match ($status) {
                'confirmed' => 'Reservation confirmed. The lot is now reserved.',
                'cancelled' => 'Reservation cancelled.',
                'completed' => 'Reservation completed. The lot is marked as sold.',
                default => 'Reservation status updated.',
            });
    }

    private function ensureOwnership(Reservation $reservation): void
    {
        abort_unless((int) $reservation->broker_id === (int) auth()->id(), 403);
    }
}