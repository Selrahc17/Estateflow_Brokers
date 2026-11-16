@extends('layouts.app')
@section('title', 'Reservation Details')
@section('page-title', 'Reservation Details')
@section('page-subtitle', '{{ $reservation->reservation_code }}')

@section('content')

@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- Reservation Info --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-800 mb-4">Reservation Info</h2>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-stone-400">Code</span><span class="font-mono text-stone-700">{{ $reservation->reservation_code }}</span></div>
                <div class="flex justify-between"><span class="text-stone-400">Status</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ ['requested' => 'bg-violet-100 text-violet-800', 'pending' => 'bg-yellow-100 text-yellow-700', 'confirmed' => 'bg-green-100 text-green-700', 'cancelled' => 'bg-red-100 text-red-700', 'completed' => 'bg-blue-100 text-blue-700'][$reservation->status] ?? 'bg-stone-100 text-stone-700' }}">
                        {{ $reservation->status_label }}
                    </span>
                </div>
                <div class="flex justify-between"><span class="text-stone-400">Total Price</span><span class="text-stone-700 font-semibold">₱{{ number_format($reservation->total_price, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-stone-400">Reservation Fee Paid</span><span class="text-stone-700">{{ $reservation->reservation_fee !== null ? '₱' . number_format($reservation->reservation_fee, 2) : '—' }}</span></div>
                <div class="flex justify-between"><span class="text-stone-400">Reserved</span><span class="text-stone-700">{{ $reservation->reserved_at?->format('M d, Y') ?? '—' }}</span></div>
                <div class="flex justify-between"><span class="text-stone-400">Expires</span><span class="text-stone-700">{{ $reservation->expires_at?->format('M d, Y') ?? '—' }}</span></div>
            </div>
        </div>

        @if($reservation->status === 'requested')
        {{-- A buyer's request: the lot is held only after the agent confirms --}}
        <div class="rounded-xl border border-violet-200 bg-violet-50 p-5">
            <h2 class="font-semibold text-violet-900">Request from the buyer</h2>
            <p class="mt-1 text-sm text-violet-900">{{ $reservation->client?->full_name ?? 'The buyer' }} asked to reserve {{ $reservation->lot?->lot_number ?? 'this lot' }}. The lot stays available until you confirm.</p>
            @error('status')<p class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>@enderror
            <div class="mt-4 flex gap-2">
                <form action="{{ route('agent.reservations.status', $reservation) }}" method="POST" class="flex-1">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="confirmed">
                    <button type="submit" class="w-full rounded-lg bg-violet-700 px-4 py-2 text-sm font-semibold text-white hover:bg-violet-800">Confirm and Reserve Lot</button>
                </form>
                <form action="{{ route('agent.reservations.status', $reservation) }}" method="POST" onsubmit="return confirm('Decline this reservation request?')">
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="cancelled">
                    <button type="submit" class="rounded-lg border border-violet-300 bg-white px-4 py-2 text-sm font-semibold text-violet-800 hover:bg-violet-100">Decline</button>
                </form>
            </div>
        </div>
        @endif

        {{-- Update Status --}}
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-800 mb-3">Update Status</h2>
            @if($reservation->status !== 'requested')
            @error('status')<p class="mb-2 text-sm font-medium text-red-700">{{ $message }}</p>@enderror
            @endif
            <form action="{{ route('agent.reservations.status', $reservation) }}" method="POST" class="flex gap-2">
                @csrf
                @method('PATCH')
                <select name="status" class="flex-1 border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                    @foreach(['pending', 'confirmed', 'cancelled', 'completed'] as $s)
                    <option value="{{ $s }}" {{ $reservation->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-teal-700 hover:bg-teal-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Update</button>
            </form>
        </div>
    </div>

    {{-- Client & Lot + Payments --}}
    <div class="xl:col-span-2 space-y-4">

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div class="bg-white rounded-xl border border-stone-200 p-5">
                <h2 class="font-semibold text-stone-800 mb-3">Client</h2>
                <p class="font-medium text-stone-700">{{ $reservation->client?->full_name ?? '—' }}</p>
                <p class="text-sm text-stone-400">{{ $reservation->client?->email }}</p>
                <p class="text-sm text-stone-400">{{ $reservation->client?->phone }}</p>
            </div>
            <div class="bg-white rounded-xl border border-stone-200 p-5">
                <h2 class="font-semibold text-stone-800 mb-3">Lot</h2>
                <p class="font-medium text-stone-700">{{ $reservation->lot?->property?->name ?? '—' }}</p>
                <p class="text-sm text-stone-400">{{ $reservation->lot?->label }}</p>
                <p class="text-sm text-stone-400">{{ $reservation->lot?->square_meters }} sqm</p>
            </div>
        </div>

        <div class="rounded-xl border border-stone-200 bg-stone-50 p-4 text-sm text-stone-600">
            Downpayment, financing and the payment schedule are handled by the developer. Record the reservation fee here; when the sale closes, mark the reservation <span class="font-medium">Completed</span> to add it to your broker's commission records.
        </div>
        @if($reservation->notes)
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-800 mb-2">Notes</h2>
            <p class="whitespace-pre-line text-sm text-stone-600">{{ $reservation->notes }}</p>
        </div>
        @endif

    </div>
</div>
@endsection
