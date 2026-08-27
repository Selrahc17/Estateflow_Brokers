@extends('layouts.app')
@section('title', 'Dashboard')
@section('page-title', 'Dashboard')
@section('page-subtitle', 'Welcome back, {{ auth()->user()->name }}!')

@section('content')

{{-- Stats Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5 mb-6">

    <div class="bg-white rounded-xl p-5 border border-stone-200 flex items-center gap-4">
        <div class="w-12 h-12 bg-amber-100 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
        </div>
        <div>
            <p class="text-sm text-stone-500">Total Properties</p>
            <p class="text-2xl font-bold text-stone-800">{{ $stats['total_properties'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl p-5 border border-stone-200 flex items-center gap-4">
        <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </div>
        <div>
            <p class="text-sm text-stone-500">Active Reservations</p>
            <p class="text-2xl font-bold text-stone-800">{{ $stats['active_reservations'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl p-5 border border-stone-200 flex items-center gap-4">
        <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </div>
        <div>
            <p class="text-sm text-stone-500">Total Clients</p>
            <p class="text-2xl font-bold text-stone-800">{{ $stats['total_clients'] }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl p-5 border border-stone-200 flex items-center gap-4">
        <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        </div>
        <div>
            <p class="text-sm text-stone-500">Pending Inquiries</p>
            <p class="text-2xl font-bold text-stone-800">{{ $stats['pending_inquiries'] ?? 0 }}</p>
        </div>
    </div>

</div>

{{-- Middle Row --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5 mb-6">

    {{-- Recent Reservations --}}
    <div class="xl:col-span-2 bg-white rounded-xl border border-stone-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-stone-800">Recent Reservations</h2>
        <a href="{{ route('agent.reservations.index') }}" class="text-xs text-amber-600 hover:underline">View all</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-left text-stone-400 border-b border-stone-100">
                        <th class="pb-3 font-medium">Client</th>
                        <th class="pb-3 font-medium">Property</th>
                        <th class="pb-3 font-medium">Lot</th>
                        <th class="pb-3 font-medium">Status</th>
                        <th class="pb-3 font-medium">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-50">
                    @forelse($recentReservations as $res)
                    <tr class="hover:bg-stone-50 transition">
                        <td class="py-3 font-medium text-stone-700">{{ $res->client?->full_name ?? '—' }}</td>
                        <td class="py-3 text-stone-500">{{ $res->lot?->property?->name ?? '—' }}</td>
                        <td class="py-3 text-stone-500">{{ $res->lot?->lot_number ?? '—' }}</td>
                        <td class="py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-medium
                                {{ $res->status === 'confirmed' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $res->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ $res->status === 'cancelled' ? 'bg-red-100 text-red-700' : '' }}
                                {{ $res->status === 'completed' ? 'bg-blue-100 text-blue-700' : '' }}">
                                {{ ucfirst($res->status) }}
                            </span>
                        </td>
                        <td class="py-3 text-stone-400 text-xs">{{ $res->reserved_at?->format('M d, Y') ?? $res->created_at->format('M d, Y') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="py-6 text-center text-stone-400 text-sm">No reservations yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Quick Actions + Alerts --}}
    <div class="space-y-5">

        {{-- Quick Actions --}}
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-800 mb-4">Quick Actions</h2>
            <div class="space-y-2">
                <a href="{{ route('agent.reservations.create') }}" class="flex items-center gap-3 p-3 rounded-lg bg-amber-50 hover:bg-amber-100 text-amber-700 transition text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    New Reservation
                </a>
                <a href="{{ route('agent.clients.create') }}" class="flex items-center gap-3 p-3 rounded-lg bg-stone-50 hover:bg-stone-100 text-stone-700 transition text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                    Add Client
                </a>
                <a href="{{ route('agent.properties.create') }}" class="flex items-center gap-3 p-3 rounded-lg bg-stone-50 hover:bg-stone-100 text-stone-700 transition text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Add Property
                </a>
            </div>
        </div>

        {{-- Recent Notifications --}}
        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h2 class="font-semibold text-stone-800 mb-4">Recent Notifications</h2>
            <div class="space-y-3">
                @php
                    $recentNotifs = \App\Models\AppNotification::where('user_id', auth()->id())->latest()->take(5)->get();
                @endphp
                @forelse($recentNotifs as $notif)
                <div class="flex items-start gap-3 p-3 {{ !$notif->is_read ? 'bg-amber-50' : 'bg-stone-50' }} rounded-lg">
                    <div class="w-2 h-2 {{ !$notif->is_read ? 'bg-amber-500' : 'bg-stone-500' }} rounded-full mt-1.5 shrink-0"></div>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-stone-700">{{ $notif->title }}</p>
                        <p class="text-xs text-stone-500">{{ $notif->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <p class="text-sm text-stone-400 text-center py-4">No notifications yet.</p>
                @endforelse
            </div>
        </div>

    </div>
</div>



@endsection
