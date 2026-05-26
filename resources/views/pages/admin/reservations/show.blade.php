@extends('layouts.admin')
@section('title', 'Reservation Details')
@section('page-title', 'Reservation Details')
@section('page-subtitle', '{{ $reservation->reservation_code }}')

@section('content')

@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-800 mb-4">Reservation Info</h2>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-stone-400">Code</span><span class="font-mono text-stone-700">{{ $reservation->reservation_code }}</span></div>
                <div class="flex justify-between"><span class="text-stone-400">Status</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $reservation->status === 'confirmed' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $reservation->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                        {{ $reservation->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                        {{ $reservation->status === 'completed' ? 'bg-blue-100 text-blue-700' : '' }}">
                        {{ ucfirst($reservation->status) }}
                    </span>
                </div>
                <div class="flex justify-between"><span class="text-stone-400">Total Price</span><span class="font-semibold text-stone-700">₱{{ number_format($reservation->total_price, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-stone-400">Down Payment</span><span class="text-stone-700">₱{{ number_format($reservation->down_payment, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-stone-400">Terms</span><span class="text-stone-700">{{ $reservation->payment_terms_months }} months</span></div>
                <div class="flex justify-between"><span class="text-stone-400">Schedule</span><span class="text-stone-700">{{ ucfirst($reservation->payment_schedule) }}</span></div>
                <div class="flex justify-between"><span class="text-stone-400">Reserved</span><span class="text-stone-700">{{ $reservation->reserved_at?->format('M d, Y') ?? '—' }}</span></div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-800 mb-3">Update Status</h2>
            <form action="{{ route('admin.reservations.status', $reservation) }}" method="POST" class="flex gap-2">
                @csrf @method('PATCH')
                <select name="status" class="flex-1 border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400">
                    @foreach(['pending','confirmed','cancelled','completed'] as $s)
                    <option value="{{ $s }}" {{ $reservation->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Update</button>
            </form>
        </div>
    </div>

    <div class="xl:col-span-2 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white rounded-xl border border-stone-200 p-4">
                <p class="text-xs text-stone-400 mb-1">Client</p>
                <p class="font-medium text-stone-700">{{ $reservation->client?->full_name ?? '—' }}</p>
                <p class="text-xs text-stone-400">{{ $reservation->client?->email }}</p>
            </div>
            <div class="bg-white rounded-xl border border-stone-200 p-4">
                <p class="text-xs text-stone-400 mb-1">Broker</p>
                <p class="font-medium text-stone-700">{{ $reservation->broker?->name ?? '—' }}</p>
                <p class="text-xs text-stone-400">{{ $reservation->broker?->email }}</p>
            </div>
            <div class="bg-white rounded-xl border border-stone-200 p-4">
                <p class="text-xs text-stone-400 mb-1">Lot</p>
                <p class="font-medium text-stone-700">{{ $reservation->lot?->property?->name ?? '—' }}</p>
                <p class="text-xs text-stone-400">Lot {{ $reservation->lot?->lot_number }}</p>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-800 mb-4">Payment History</h2>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-stone-400 border-b border-stone-100">
                        <th class="pb-3 font-medium">Code</th>
                        <th class="pb-3 font-medium">Amount</th>
                        <th class="pb-3 font-medium">Type</th>
                        <th class="pb-3 font-medium">Status</th>
                        <th class="pb-3 font-medium">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-50">
                    @forelse($reservation->payments as $payment)
                    <tr class="hover:bg-stone-50">
                        <td class="py-3 font-mono text-xs text-stone-600">{{ $payment->payment_code }}</td>
                        <td class="py-3 font-semibold text-stone-700">₱{{ number_format($payment->amount, 2) }}</td>
                        <td class="py-3 text-stone-500">{{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}</td>
                        <td class="py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $payment->status === 'verified' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $payment->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ $payment->status === 'failed' ? 'bg-red-100 text-red-700' : '' }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                        <td class="py-3 text-stone-400 text-xs">{{ $payment->created_at->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-6 text-center text-stone-400">No payments recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>
@endsection
