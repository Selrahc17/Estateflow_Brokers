@extends('layouts.public')
@section('title', 'My Reservation')

@section('content')

<div class="bg-gradient-to-r from-stone-900 to-amber-900 text-white">
    <div class="max-w-6xl mx-auto px-6 py-10">
        <p class="text-amber-400 text-xs uppercase tracking-widest font-semibold mb-1">My Reservations</p>
        <h1 class="text-2xl font-bold">Reservation History</h1>
    </div>
</div>

<div class="max-w-6xl mx-auto px-6 py-8 space-y-5">

    @forelse($reservations as $res)
    <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-stone-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <p class="font-bold text-stone-800">{{ $res->lot?->property?->name ?? '—' }}</p>
                <p class="text-sm text-stone-400">Lot {{ $res->lot?->lot_number }} · Code: {{ $res->reservation_code }}</p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-semibold self-start sm:self-auto
                {{ $res->status==='confirmed' ? 'bg-green-100 text-green-700' : '' }}
                {{ $res->status==='pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                {{ $res->status==='cancelled' ? 'bg-red-100 text-red-600' : '' }}
                {{ $res->status==='completed' ? 'bg-blue-100 text-blue-700' : '' }}">
                {{ ucfirst($res->status) }}
            </span>
        </div>
        <div class="p-6 grid grid-cols-2 sm:grid-cols-4 gap-4">
            <div class="p-4 bg-stone-50 rounded-xl">
                <p class="text-xs text-stone-400 mb-1">Total Price</p>
                <p class="text-sm font-bold text-green-600">₱{{ number_format($res->total_price, 2) }}</p>
            </div>
            <div class="p-4 bg-stone-50 rounded-xl">
                <p class="text-xs text-stone-400 mb-1">Down Payment</p>
                <p class="text-sm font-bold text-stone-700">₱{{ number_format($res->down_payment, 2) }}</p>
            </div>
            <div class="p-4 bg-stone-50 rounded-xl">
                <p class="text-xs text-stone-400 mb-1">Monthly Payment</p>
                <p class="text-sm font-bold text-amber-600">₱{{ number_format($res->monthly_payment, 2) }}</p>
            </div>
            <div class="p-4 bg-stone-50 rounded-xl">
                <p class="text-xs text-stone-400 mb-1">Terms</p>
                <p class="text-sm font-bold text-stone-700">{{ $res->payment_terms_months }} months</p>
            </div>
        </div>

        @if($res->payments->isNotEmpty())
        @php $paid = $res->payments->where('status','verified')->sum('amount'); $pct = $res->total_price > 0 ? min(100, round($paid/$res->total_price*100)) : 0; @endphp
        <div class="px-6 pb-6">
            <div class="flex justify-between text-xs text-stone-500 mb-1">
                <span>Payment Progress</span>
                <span class="font-semibold text-amber-600">{{ $pct }}%</span>
            </div>
            <div class="w-full bg-stone-100 rounded-full h-2 overflow-hidden">
                <div class="bg-amber-500 h-2 rounded-full" style="width: {{ $pct }}%"></div>
            </div>
            <div class="flex justify-between text-xs text-stone-400 mt-1">
                <span>₱{{ number_format($paid, 0) }} paid</span>
                <span>₱{{ number_format($res->total_price - $paid, 0) }} remaining</span>
            </div>
        </div>
        @endif

        <div class="px-6 pb-5">
        </div>
    </div>
    @empty
    <div class="bg-white rounded-2xl border border-stone-200 py-16 text-center">
        <svg class="w-16 h-16 text-stone-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        <p class="text-stone-400 font-medium">No reservations yet.</p>
        <a href="{{ route('client.properties') }}" class="mt-3 inline-block bg-amber-600 hover:bg-amber-700 text-white px-5 py-2 rounded-xl text-sm font-medium transition">Browse Properties</a>
    </div>
    @endforelse

    <div>{{ $reservations->links() }}</div>
</div>

@endsection
