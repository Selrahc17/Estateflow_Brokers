@extends('layouts.app')
@section('title', 'Reservations')
@section('page-title', 'Reservations')
@section('page-subtitle', 'Manage all client reservations')

@section('content')

@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
    @foreach([
        ['Requests from Buyers', (int) $counts->get('requested', 0), 'text-violet-700', 'requested'],
        ['Pending', (int) $counts->get('pending', 0), 'text-yellow-600', 'pending'],
        ['Confirmed', (int) $counts->get('confirmed', 0), 'text-green-600', 'confirmed'],
        ['Completed', (int) $counts->get('completed', 0), 'text-blue-700', 'completed'],
    ] as [$label, $count, $color, $key])
    <a href="{{ route('agent.reservations.index', $status === $key ? [] : ['status' => $key]) }}" class="rounded-xl border bg-white p-4 text-center transition hover:border-teal-300 {{ $status === $key ? 'border-teal-500 ring-2 ring-teal-100' : 'border-stone-200' }}">
        <p class="text-2xl font-bold {{ $color }}">{{ $count }}</p>
        <p class="text-sm text-stone-500">{{ $label }}</p>
    </a>
    @endforeach
</div>

<div class="flex flex-col sm:flex-row gap-3 justify-between mb-5">
    <form method="GET" class="flex flex-wrap gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search client or lot..." class="min-w-0 flex-1 sm:flex-none border border-stone-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 w-56">
        <select name="status" class="border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400" onchange="this.form.submit()">
            <option value="">All Status</option>
            @foreach(array_keys(\App\Models\Reservation::STATUSES) as $s)
            <option value="{{ $s }}" @selected($status === $s)>{{ \App\Models\Reservation::STATUSES[$s] }}</option>
            @endforeach
        </select>
    </form>
    <a href="{{ route('agent.reservations.create') }}" class="bg-teal-700 hover:bg-teal-800 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        New Reservation
    </a>
</div>

<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    <div class="overflow-x-auto">
    <table class="w-full text-sm">
        <thead class="bg-stone-50 border-b border-stone-200">
            <tr class="text-left text-stone-500">
                <th class="px-5 py-3 font-medium">Code</th>
                <th class="px-5 py-3 font-medium">Client</th>
                <th class="px-5 py-3 font-medium">Property / Lot</th>
                <th class="px-5 py-3 font-medium">Total</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium">Date</th>
                <th class="px-5 py-3 font-medium"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @forelse($reservations as $res)
            <tr class="hover:bg-stone-50 transition">
                <td class="px-5 py-3 font-mono text-xs text-stone-500">{{ $res->reservation_code }}</td>
                <td class="px-5 py-3 font-medium text-stone-700">{{ $res->client?->full_name ?? '—' }}</td>
                <td class="px-5 py-3 text-stone-500 text-xs">{{ $res->lot?->property?->name }} / {{ $res->lot?->label }}</td>
                <td class="px-5 py-3 font-semibold text-stone-700">₱{{ number_format($res->total_price, 2) }}</td>
                <td class="px-5 py-3">
                    <span class="whitespace-nowrap px-2 py-1 rounded-full text-xs font-medium {{ ['requested' => 'bg-violet-100 text-violet-800', 'pending' => 'bg-yellow-100 text-yellow-700', 'confirmed' => 'bg-green-100 text-green-700', 'cancelled' => 'bg-red-100 text-red-600', 'completed' => 'bg-blue-100 text-blue-700'][$res->status] ?? 'bg-stone-100 text-stone-700' }}">
                        {{ $res->status_label }}
                    </span>
                </td>
                <td class="px-5 py-3 text-stone-400 text-xs">{{ $res->created_at->format('M d, Y') }}</td>
                <td class="px-5 py-3">
                    <a href="{{ route('agent.reservations.show', $res) }}" class="text-xs text-teal-700 hover:underline">View</a>
                </td>
            </tr>
            @empty
            <tr><td colspan="7" class="px-5 py-10 text-center text-stone-400">No reservations yet. <a href="{{ route('agent.reservations.create') }}" class="text-teal-700 hover:underline">Create one</a></td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
    <div class="px-5 py-4 border-t border-stone-100">{{ $reservations->withQueryString()->links() }}</div>
</div>

@endsection
