@extends('layouts.admin')
@section('title', 'Reservation Details')
@section('page-title', 'Reservation Details')
@section('page-subtitle', $reservation->reservation_code)

@section('content')

@if(session('success'))
<div class="mb-5 rounded-lg border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-950 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
@endif
@if($errors->any())
<div role="alert" class="mb-5 rounded-lg border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950 px-4 py-3 text-sm text-red-700 dark:text-red-300">{{ $errors->first() }}</div>
@endif

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    <div class="space-y-4">
        <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-5">
            <h2 class="font-semibold text-stone-800 dark:text-stone-100 mb-4">Reservation Info</h2>
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span class="text-stone-500 dark:text-stone-400">Code</span><span class="font-mono text-stone-700 dark:text-stone-200">{{ $reservation->reservation_code }}</span></div>
                <div class="flex justify-between"><span class="text-stone-500 dark:text-stone-400">Status</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $reservation->status === 'confirmed' ? 'bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300' : '' }}
                        {{ $reservation->status === 'pending' ? 'bg-yellow-100 dark:bg-yellow-950 text-yellow-700 dark:text-yellow-300' : '' }}
                        {{ $reservation->status === 'cancelled' ? 'bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300' : '' }}
                        {{ $reservation->status === 'completed' ? 'bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300' : '' }}">
                        {{ ucfirst($reservation->status) }}
                    </span>
                </div>
                <div class="flex justify-between"><span class="text-stone-500 dark:text-stone-400">Total Price</span><span class="font-semibold text-stone-700 dark:text-stone-200">₱{{ number_format($reservation->total_price, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-stone-500 dark:text-stone-400">Down Payment</span><span class="text-stone-700 dark:text-stone-200">₱{{ number_format($reservation->down_payment, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-stone-500 dark:text-stone-400">Terms</span><span class="text-stone-700 dark:text-stone-200">{{ $reservation->payment_terms_months }} months</span></div>
                <div class="flex justify-between"><span class="text-stone-500 dark:text-stone-400">Schedule</span><span class="text-stone-700 dark:text-stone-200">{{ ucfirst($reservation->payment_schedule) }}</span></div>
                <div class="flex justify-between"><span class="text-stone-500 dark:text-stone-400">Reserved</span><span class="text-stone-700 dark:text-stone-200">{{ $reservation->reserved_at?->format('M d, Y') ?? '—' }}</span></div>
            </div>
        </div>

        <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-5">
            <h2 class="font-semibold text-stone-800 dark:text-stone-100 mb-3">Update Status</h2>
            <form action="{{ route('admin.reservations.status', $reservation) }}" method="POST" class="flex gap-2">
                @csrf @method('PATCH')
                <select name="status" class="flex-1 border border-stone-200 dark:border-stone-700 dark:bg-stone-800 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                    @foreach(['pending','confirmed','cancelled','completed'] as $s)
                    <option value="{{ $s }}" {{ $reservation->status === $s ? 'selected' : '' }}>{{ ucfirst($s) }}</option>
                    @endforeach
                </select>
                <button type="submit" class="bg-teal-700 hover:bg-teal-800 text-white px-4 py-2 rounded-lg text-sm font-medium transition">Update</button>
            </form>
        </div>
    </div>

    <div class="xl:col-span-2 space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-4">
                <p class="text-xs text-stone-500 dark:text-stone-400 mb-1">Client</p>
                <p class="font-medium text-stone-700 dark:text-stone-200">{{ $reservation->client?->full_name ?? '—' }}</p>
                <p class="text-xs text-stone-500 dark:text-stone-400">{{ $reservation->client?->email }}</p>
            </div>
            <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-4">
                <p class="text-xs text-stone-500 dark:text-stone-400 mb-1">Broker</p>
                <p class="font-medium text-stone-700 dark:text-stone-200">{{ $reservation->broker?->name ?? '—' }}</p>
                <p class="text-xs text-stone-500 dark:text-stone-400">{{ $reservation->broker?->email }}</p>
            </div>
            <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-4">
                <p class="text-xs text-stone-500 dark:text-stone-400 mb-1">Lot</p>
                <p class="font-medium text-stone-700 dark:text-stone-200">{{ $reservation->lot?->property?->name ?? '—' }}</p>
                <p class="text-xs text-stone-500 dark:text-stone-400">Lot {{ $reservation->lot?->lot_number }}</p>
            </div>
        </div>

        <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-5">
            <h2 class="font-semibold text-stone-800 dark:text-stone-100 mb-4">Payment History</h2>
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-stone-500 dark:text-stone-400 border-b border-stone-100 dark:border-stone-800">
                        <th class="pb-3 font-medium">Code</th>
                        <th class="pb-3 font-medium">Amount</th>
                        <th class="pb-3 font-medium">Type</th>
                        <th class="pb-3 font-medium">Status</th>
                        <th class="pb-3 font-medium">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-50 dark:divide-stone-800">
                    @forelse($reservation->payments as $payment)
                    <tr class="hover:bg-stone-50 dark:hover:bg-stone-800">
                        <td class="py-3 font-mono text-xs text-stone-600 dark:text-stone-300">{{ $payment->payment_code }}</td>
                        <td class="py-3 font-semibold text-stone-700 dark:text-stone-200">₱{{ number_format($payment->amount, 2) }}</td>
                        <td class="py-3 text-stone-500 dark:text-stone-400">{{ ucfirst(str_replace('_', ' ', $payment->payment_type)) }}</td>
                        <td class="py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $payment->status === 'verified' ? 'bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300' : '' }}
                                {{ $payment->status === 'pending' ? 'bg-yellow-100 dark:bg-yellow-950 text-yellow-700 dark:text-yellow-300' : '' }}
                                {{ $payment->status === 'failed' ? 'bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300' : '' }}">
                                {{ ucfirst($payment->status) }}
                            </span>
                        </td>
                        <td class="py-3 text-stone-500 dark:text-stone-400 text-xs">{{ $payment->created_at->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-6 text-center text-stone-500 dark:text-stone-400">No payments recorded.</td></tr>
                    @endforelse
                </tbody>
            </table>
            </div>
        </div>
    </div>

</div>
@endsection
