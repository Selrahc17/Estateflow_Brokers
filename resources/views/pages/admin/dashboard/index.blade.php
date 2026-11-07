@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Admin Dashboard')
@section('page-subtitle', 'System-wide overview and key metrics')

@section('content')

<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
    @foreach([
        ['Total Users', $stats['total_users'], 'M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z'],
        ['Brokers', $stats['total_brokers'], 'M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
        ['Clients', $stats['total_clients'], 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'],
        ['Properties', $stats['total_properties'], 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
        ['Reservations', $stats['total_reservations'], 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
    ] as [$label, $value, $icon])
        @include('layouts.partials.stat-tile', compact('label', 'value', 'icon'))
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- Recent Reservations --}}
    <div class="xl:col-span-2 bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100 dark:border-stone-800">
            <h2 class="font-semibold text-stone-800 dark:text-stone-100">Recent Reservations</h2>
            <a href="{{ route('admin.reservations') }}" class="text-xs text-teal-700 dark:text-teal-300 hover:underline">View all →</a>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 dark:bg-stone-800/60 border-b border-stone-100 dark:border-stone-800">
                <tr class="text-left text-stone-500 dark:text-stone-400">
                    <th class="px-5 py-3 font-medium">Client</th>
                    <th class="px-5 py-3 font-medium">Broker</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Date</th>
                    <th class="px-5 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                @forelse($recentReservations as $res)
                <tr class="hover:bg-stone-50 dark:hover:bg-stone-800 transition">
                    <td class="px-5 py-3 font-medium text-stone-700 dark:text-stone-200">{{ $res->client?->full_name ?? '—' }}</td>
                    <td class="px-5 py-3 text-stone-500 dark:text-stone-400 text-xs">{{ $res->broker?->name ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $res->status==='confirmed' ? 'bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300' : '' }}
                            {{ $res->status==='pending' ? 'bg-yellow-100 dark:bg-yellow-950 text-yellow-700 dark:text-yellow-300' : '' }}
                            {{ $res->status==='cancelled' ? 'bg-red-100 dark:bg-red-950 text-red-600 dark:text-red-300' : '' }}
                            {{ $res->status==='completed' ? 'bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300' : '' }}">
                            {{ ucfirst($res->status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-stone-500 dark:text-stone-400 text-xs whitespace-nowrap">{{ $res->created_at->format('M d, Y') }}</td>
                    <td class="px-5 py-3">
                        <a href="{{ route('admin.reservations.show', $res) }}" class="text-xs text-teal-700 dark:text-teal-300 hover:underline">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-8 text-center text-stone-500 dark:text-stone-400">No reservations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    {{-- Recent Users --}}
    <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100 dark:border-stone-800">
            <h2 class="font-semibold text-stone-800 dark:text-stone-100">Recent Users</h2>
            <a href="{{ route('admin.users') }}" class="text-xs text-teal-700 dark:text-teal-300 hover:underline">View all →</a>
        </div>
        <div class="space-y-3 p-5">
            @forelse($recentUsers as $user)
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 {{ $user->role==='agent' ? 'bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200' : ($user->role==='admin' ? 'bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300' : 'bg-blue-100 dark:bg-blue-950 text-blue-700 dark:text-blue-300') }} rounded-full flex items-center justify-center text-xs font-bold shrink-0">
                    {{ strtoupper(substr($user->name,0,1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-stone-700 dark:text-stone-200 truncate">{{ $user->name }}</p>
                    <p class="text-xs text-stone-500 dark:text-stone-400">{{ ucfirst($user->role) }} · {{ $user->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @empty
            <p class="text-stone-500 dark:text-stone-400 text-sm text-center py-4">No users yet.</p>
            @endforelse
        </div>
    </div>

</div>

@endsection
