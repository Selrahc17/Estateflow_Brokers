@extends('layouts.public')
@section('title', 'My Account')

@section('content')

<div class="bg-gradient-to-r from-stone-900 to-amber-900 text-white">
    <div class="max-w-6xl mx-auto px-6 py-12 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <div class="w-16 h-16 bg-amber-500 rounded-2xl flex items-center justify-center text-white text-2xl font-bold shrink-0">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div>
                <p class="text-amber-400 text-xs uppercase tracking-widest font-semibold mb-1">Welcome back</p>
                <h1 class="text-2xl font-bold">{{ auth()->user()->name }}</h1>
                <p class="text-stone-300 text-sm mt-0.5">{{ auth()->user()->email }} · Client since {{ auth()->user()->created_at->format('F Y') }}</p>
            </div>
        </div>
        <a href="{{ route('client.account.profile') }}" class="flex items-center gap-2 bg-white/10 hover:bg-white/20 border border-white/20 text-white px-4 py-2.5 rounded-xl text-sm font-medium transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            Edit Profile
        </a>
    </div>
</div>

<div class="max-w-6xl mx-auto px-6 -mt-5">
    <div class="grid grid-cols-2 sm:grid-cols-2 gap-4">
        <div class="bg-white rounded-2xl border border-green-100 p-4 shadow-sm">
            <p class="text-xs text-stone-500 mb-1">Active Reservations</p>
            <p class="text-xl font-bold text-green-600">{{ $stats['active_reservation'] }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-amber-100 p-4 shadow-sm">
            <p class="text-xs text-stone-500 mb-1">Pending Payments</p>
            <p class="text-xl font-bold text-amber-600">{{ $stats['pending_payments'] }}</p>
        </div>
    </div>
</div>

<div class="max-w-6xl mx-auto px-6 py-8">
    <h2 class="font-semibold text-stone-800 mb-4">Quick Access</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-8">
        @foreach([
            ['My Reservation','client.account.reservation','M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
            ['My Payments','client.account.payments','M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
            ['My Documents','client.account.documents','M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['Notifications','client.account.notifications','M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
            ['Feedback','client.account.feedback','M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z'],
        ] as $nav)
        <a href="{{ route($nav[1]) }}" class="bg-white rounded-2xl border border-stone-200 p-5 flex flex-col items-center gap-3 hover:shadow-md hover:border-amber-200 transition group text-center">
            <div class="w-12 h-12 bg-stone-100 group-hover:bg-amber-100 rounded-xl flex items-center justify-center transition">
                <svg class="w-6 h-6 text-stone-500 group-hover:text-amber-600 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $nav[2] }}"/></svg>
            </div>
            <p class="text-sm font-semibold text-stone-700 group-hover:text-amber-700 transition">{{ $nav[0] }}</p>
        </a>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Recent Reservations --}}
        <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-stone-100 flex items-center justify-between">
                <h2 class="font-semibold text-stone-800">My Reservations</h2>
                <a href="{{ route('client.account.reservation') }}" class="text-xs text-amber-600 hover:underline">View all →</a>
            </div>
            <div class="divide-y divide-stone-100">
                @forelse($recentReservations as $res)
                <div class="px-5 py-4">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="font-semibold text-stone-800 text-sm">{{ $res->lot?->property?->name ?? '—' }}</p>
                            <p class="text-xs text-stone-400 mt-0.5">Lot {{ $res->lot?->lot_number }} · {{ $res->reservation_code }}</p>
                        </div>
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $res->status==='confirmed' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $res->status==='pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $res->status==='cancelled' ? 'bg-red-100 text-red-600' : '' }}">
                            {{ ucfirst($res->status) }}
                        </span>
                    </div>
                    <p class="text-xs text-stone-500 mt-1">₱{{ number_format($res->total_price, 2) }} total</p>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-stone-400 text-sm">No reservations yet.</div>
                @endforelse
            </div>
        </div>

        {{-- Recent Notifications --}}
        <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
            <div class="px-5 py-4 border-b border-stone-100 flex items-center justify-between">
                <h2 class="font-semibold text-stone-800">Recent Notifications</h2>
                <a href="{{ route('client.account.notifications') }}" class="text-xs text-amber-600 hover:underline">See all →</a>
            </div>
            <div class="divide-y divide-stone-100">
                @forelse($notifications as $notif)
                <div class="flex items-start gap-3 px-5 py-3.5 {{ !$notif->is_read ? 'bg-amber-50/50' : '' }}">
                    <div class="w-2 h-2 rounded-full mt-1.5 shrink-0 {{ $notif->is_read ? 'bg-stone-300' : 'bg-amber-500' }}"></div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-stone-700">{{ $notif->title }}</p>
                        <p class="text-xs text-stone-400">{{ $notif->message }}</p>
                    </div>
                    <span class="text-xs text-stone-400 shrink-0">{{ $notif->created_at->diffForHumans() }}</span>
                </div>
                @empty
                <div class="px-5 py-8 text-center text-stone-400 text-sm">No notifications.</div>
                @endforelse
            </div>
        </div>

    </div>
</div>

@endsection
