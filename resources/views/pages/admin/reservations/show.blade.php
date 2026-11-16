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
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ ['requested' => 'bg-violet-100 dark:bg-violet-950 text-violet-800 dark:text-violet-300', 'pending' => 'bg-yellow-100 dark:bg-yellow-950 text-yellow-700 dark:text-yellow-300', 'confirmed' => 'bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300', 'cancelled' => 'bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300', 'completed' => 'bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300'][$reservation->status] ?? 'bg-stone-100 text-stone-700' }}">
                        {{ $reservation->status_label }}
                    </span>
                </div>
                <div class="flex justify-between"><span class="text-stone-500 dark:text-stone-400">Total Price</span><span class="font-semibold text-stone-700 dark:text-stone-200">₱{{ number_format($reservation->total_price, 2) }}</span></div>
                <div class="flex justify-between"><span class="text-stone-500 dark:text-stone-400">Reservation Fee Paid</span><span class="text-stone-700 dark:text-stone-200">{{ $reservation->reservation_fee !== null ? '₱' . number_format($reservation->reservation_fee, 2) : '—' }}</span></div>
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
                <p class="text-xs text-stone-500 dark:text-stone-400">{{ $reservation->lot?->label }}</p>
            </div>
        </div>

        @if($reservation->notes)
        <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-5">
            <h2 class="font-semibold text-stone-800 dark:text-stone-100 mb-2">Notes</h2>
            <p class="whitespace-pre-line text-sm text-stone-600 dark:text-stone-300">{{ $reservation->notes }}</p>
        </div>
        @endif
    </div>

</div>
@endsection
