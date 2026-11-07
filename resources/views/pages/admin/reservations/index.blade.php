@extends('layouts.admin')
@section('title', 'Reservations')
@section('page-title', 'Reservation Management')
@section('page-subtitle', 'Review all client reservations')

@section('content')

@if(session('success'))
<div class="mb-5 rounded-lg border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-950 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
@endif

<div class="flex flex-wrap gap-3 justify-between mb-5">
    <form method="GET" class="flex gap-2 flex-wrap">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search client..." class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-700 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 w-full sm:w-48">
        <select name="status" class="bg-white dark:bg-stone-900 border border-stone-200 dark:border-stone-700 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400" onchange="this.form.submit()">
            <option value="">All Status</option>
            @foreach(['pending','confirmed','cancelled','completed'] as $s)
            <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-stone-50 dark:bg-stone-800/60 border-b border-stone-200 dark:border-stone-700">
            <tr class="text-left text-stone-500 dark:text-stone-400">
                <th class="px-5 py-3 font-medium">Code</th>
                <th class="px-5 py-3 font-medium">Client</th>
                <th class="px-5 py-3 font-medium">Broker</th>
                <th class="px-5 py-3 font-medium">Total</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium">Date</th>
                <th class="px-5 py-3 font-medium">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
            @forelse($reservations as $res)
            <tr class="hover:bg-stone-50 dark:hover:bg-stone-800 transition">
                <td class="px-5 py-3 font-mono text-xs text-stone-500 dark:text-stone-400">{{ $res->reservation_code }}</td>
                <td class="px-5 py-3 font-medium text-stone-700 dark:text-stone-200">{{ $res->client?->full_name ?? '—' }}</td>
                <td class="px-5 py-3 text-stone-500 dark:text-stone-400 text-xs">{{ $res->broker?->name ?? '—' }}</td>
                <td class="px-5 py-3 font-semibold text-stone-700 dark:text-stone-200">₱{{ number_format($res->total_price, 2) }}</td>
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
            <tr><td colspan="7" class="px-5 py-10 text-center text-stone-500 dark:text-stone-400">No reservations found.</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="px-5 py-4 border-t border-stone-100 dark:border-stone-800">{{ $reservations->withQueryString()->links() }}</div>
</div>

@endsection
