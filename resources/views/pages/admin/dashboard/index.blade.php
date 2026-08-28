@extends('layouts.admin')
@section('title', 'Dashboard')
@section('page-title', 'Admin Dashboard')
@section('page-subtitle', 'System-wide overview and key metrics')

@section('content')

<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 mb-6">
    @foreach([
        ['Total Users', $stats['total_users'], 'bg-blue-50', 'text-blue-600'],
        ['Brokers', $stats['total_brokers'], 'bg-teal-50', 'text-teal-700'],
        ['Clients', $stats['total_clients'], 'bg-indigo-50', 'text-indigo-600'],
        ['Properties', $stats['total_properties'], 'bg-green-50', 'text-green-600'],
        ['Reservations', $stats['total_reservations'], 'bg-purple-50', 'text-purple-600'],
    ] as $s)
    <div class="bg-white rounded-xl border border-stone-200 p-4 text-center">
        <p class="text-xl font-bold {{ $s[3] }}">{{ $s[1] }}</p>
        <p class="text-xs text-stone-500 mt-1">{{ $s[0] }}</p>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">

    {{-- Recent Reservations --}}
    <div class="xl:col-span-2 bg-white rounded-xl border border-stone-200">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
            <h2 class="font-semibold text-stone-800">Recent Reservations</h2>
            <a href="{{ route('admin.reservations') }}" class="text-xs text-red-600 hover:underline">View all →</a>
        </div>
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-stone-100">
                <tr class="text-left text-stone-500">
                    <th class="px-5 py-3 font-medium">Client</th>
                    <th class="px-5 py-3 font-medium">Broker</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Date</th>
                    <th class="px-5 py-3 font-medium"></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse($recentReservations as $res)
                <tr class="hover:bg-stone-50 transition">
                    <td class="px-5 py-3 font-medium text-stone-700">{{ $res->client?->full_name ?? '—' }}</td>
                    <td class="px-5 py-3 text-stone-500 text-xs">{{ $res->broker?->name ?? '—' }}</td>
                    <td class="px-5 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            {{ $res->status==='confirmed' ? 'bg-green-100 text-green-700' : '' }}
                            {{ $res->status==='pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
                            {{ $res->status==='cancelled' ? 'bg-red-100 text-red-600' : '' }}
                            {{ $res->status==='completed' ? 'bg-blue-100 text-blue-700' : '' }}">
                            {{ ucfirst($res->status) }}
                        </span>
                    </td>
                    <td class="px-5 py-3 text-stone-400 text-xs">{{ $res->created_at->format('M d, Y') }}</td>
                    <td class="px-5 py-3">
                        <a href="{{ route('admin.reservations.show', $res) }}" class="text-xs text-red-600 hover:underline">View</a>
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-8 text-center text-stone-400">No reservations yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Recent Users --}}
    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-stone-800">Recent Users</h2>
            <a href="{{ route('admin.users') }}" class="text-xs text-red-600 hover:underline">View all →</a>
        </div>
        <div class="space-y-3">
            @forelse($recentUsers as $user)
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 {{ $user->role==='agent' ? 'bg-teal-100 text-teal-800' : ($user->role==='admin' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') }} rounded-full flex items-center justify-center text-xs font-bold shrink-0">
                    {{ strtoupper(substr($user->name,0,1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-stone-700 truncate">{{ $user->name }}</p>
                    <p class="text-xs text-stone-400">{{ ucfirst($user->role) }} · {{ $user->created_at->diffForHumans() }}</p>
                </div>
            </div>
            @empty
            <p class="text-stone-400 text-sm text-center py-4">No users yet.</p>
            @endforelse
        </div>
    </div>

</div>

@endsection
