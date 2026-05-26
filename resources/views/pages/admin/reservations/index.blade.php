@extends('layouts.admin')
@section('title', 'Reservations')
@section('page-title', 'Reservation Management')
@section('page-subtitle', 'Review all client reservations')

@section('content')

@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

<div class="flex flex-wrap gap-3 justify-between mb-5">
    <form method="GET" class="flex gap-2 flex-wrap">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search client..." class="border border-stone-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400 w-48">
        <select name="status" class="border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-red-400" onchange="this.form.submit()">
            <option value="">All Status</option>
            @foreach(['pending','confirmed','cancelled','completed'] as $s)
            <option value="{{ $s }}" {{ request('status')===$s?'selected':'' }}>{{ ucfirst($s) }}</option>
            @endforeach
        </select>
    </form>
</div>

<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    <table class="w-full text-sm">
        <thead class="bg-stone-50 border-b border-stone-200">
            <tr class="text-left text-stone-500">
                <th class="px-5 py-3 font-medium">Code</th>
                <th class="px-5 py-3 font-medium">Client</th>
                <th class="px-5 py-3 font-medium">Broker</th>
                <th class="px-5 py-3 font-medium">Total</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium">Date</th>
                <th class="px-5 py-3 font-medium">Actions</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @forelse($reservations as $res)
            <tr class="hover:bg-stone-50 transition">
                <td class="px-5 py-3 font-mono text-xs text-stone-500">{{ $res->reservation_code }}</td>
                <td class="px-5 py-3 font-medium text-stone-700">{{ $res->client?->full_name ?? '—' }}</td>
                <td class="px-5 py-3 text-stone-500 text-xs">{{ $res->broker?->name ?? '—' }}</td>
                <td class="px-5 py-3 font-semibold text-stone-700">₱{{ number_format($res->total_price, 2) }}</td>
                <td class="px-5 py-3">
                    <span class="px-2 py-1 rounded-full text-xs font-medium
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
            <tr><td colspan="7" class="px-5 py-10 text-center text-stone-400">No reservations found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="px-5 py-4 border-t border-stone-100">{{ $reservations->withQueryString()->links() }}</div>
</div>

@endsection
