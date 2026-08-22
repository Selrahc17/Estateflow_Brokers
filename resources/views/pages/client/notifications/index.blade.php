@extends('layouts.public')
@section('title', 'Notifications')

@section('content')

{{-- Page Hero --}}
<div class="bg-gradient-to-r from-stone-900 to-amber-900 text-white">
    <div class="max-w-6xl mx-auto px-6 py-10 flex items-center justify-between">
        <div>
            <p class="text-amber-400 text-xs uppercase tracking-widest font-semibold mb-1">Notifications</p>
            <h1 class="text-2xl font-bold">Your Alerts & Updates</h1>
            <p class="text-stone-300 text-sm mt-1">Stay updated on your reservation, payments, and documents</p>
        </div>
        @php
            $unread = $notifications->where('is_read', false)->count();
        @endphp
        @if($unread > 0)
            <div class="hidden sm:flex items-center gap-2 bg-white/10 px-4 py-2 rounded-xl">
                <span class="w-2.5 h-2.5 bg-red-400 rounded-full"></span>
                <span class="text-sm font-semibold">{{ $unread }} unread</span>
            </div>
        @endif
    </div>
</div>

<div class="max-w-6xl mx-auto px-6 py-8">

    <div class="flex justify-between items-center mb-6">
        <p class="text-sm text-stone-500">{{ $notifications->total() }} notification(s)</p>
        <form action="{{ route('client.account.notifications.read-all') }}" method="POST">
            @csrf
            <button type="submit" class="text-xs text-amber-600 hover:underline">Mark all as read</button>
        </form>
    </div>

    <div class="space-y-3">
        @forelse($notifications as $notif)
        <div class="bg-white rounded-2xl border border-stone-200 p-5 flex items-start gap-4 hover:shadow-sm transition
            {{ !$notif->is_read ? 'border-l-4 border-l-amber-500' : '' }}">
            <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 bg-amber-100">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2 mb-1">
                    <p class="font-semibold text-stone-800 text-sm">{{ $notif->title }}</p>
                    <div class="flex items-center gap-2 shrink-0">
                        @if(!$notif->is_read)
                        <span class="bg-amber-100 text-amber-700 text-xs font-semibold px-2 py-0.5 rounded-full">New</span>
                        @endif
                        <span class="text-xs text-stone-400">{{ $notif->created_at->diffForHumans() }}</span>
                    </div>
                </div>
                <p class="text-sm text-stone-500 leading-relaxed">{{ $notif->message }}</p>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-stone-200 p-10 text-center text-stone-400">
            No notifications yet.
        </div>
        @endforelse
    </div>

    <div class="mt-4">{{ $notifications->links() }}</div>

</div>

@endsection
