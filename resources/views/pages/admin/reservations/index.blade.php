@extends('layouts.admin')
@section('title', 'Reservations')
@section('page-title', 'Reservation Management')
@section('page-subtitle', 'Monitor lot reservations across all brokers')

@section('content')
@php
    $fieldClass = 'rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400';
    $tabAccent = [
        'all' => 'text-stone-700 dark:text-stone-200',
        'requested' => 'text-violet-700 dark:text-violet-300',
        'pending' => 'text-amber-700 dark:text-amber-300',
        'confirmed' => 'text-teal-700 dark:text-teal-300',
        'completed' => 'text-green-700 dark:text-green-300',
        'cancelled' => 'text-stone-500 dark:text-stone-400',
    ];
    $tabLabel = ['requested' => 'Requested'];
    $keep = array_filter(['search' => $search, 'broker' => $brokerId]);
    $agentOf = fn ($res) => $res->broker;
    $brokerOf = fn ($res) => $res->broker?->role === 'agent' ? $res->broker->broker : $res->broker;
@endphp

@if(session('success'))
<div class="mb-5 rounded-lg border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-950 px-4 py-3 text-sm text-green-700 dark:text-green-300" role="status">{{ session('success') }}</div>
@endif

{{-- Status tabs with counts --}}
<nav aria-label="Reservation status" class="mb-5 grid grid-cols-3 gap-3 lg:grid-cols-6">
    @foreach($tabs as $value => $label)
    @php $active = $status === $value; @endphp
    <a href="{{ route('admin.reservations', $keep + ['status' => $value]) }}" @if($active) aria-current="page" @endif
       class="rounded-xl border px-4 py-3 transition {{ $active ? 'border-teal-600 bg-white ring-2 ring-teal-600/20 dark:bg-stone-900' : 'border-stone-200 bg-white hover:border-stone-300 dark:border-stone-700 dark:bg-stone-900 dark:hover:border-stone-600' }}">
        <span class="block truncate text-xs font-medium text-stone-500 dark:text-stone-400">{{ $tabLabel[$value] ?? $label }}</span>
        <span class="mt-1 block text-2xl font-bold {{ $tabAccent[$value] ?? $tabAccent['all'] }}">{{ $counts[$value] ?? 0 }}</span>
    </a>
    @endforeach
</nav>

{{-- Search and filter --}}
<form method="GET" class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-[1fr_220px_auto]">
    <input type="hidden" name="status" value="{{ $status }}">
    <input type="search" name="search" value="{{ $search }}" placeholder="Search code, client, property, or agent..." aria-label="Search reservations" class="{{ $fieldClass }}">
    <select name="broker" aria-label="Filter by broker" class="{{ $fieldClass }}">
        <option value="">All brokers</option>
        @foreach($brokers as $broker)
        <option value="{{ $broker->id }}" @selected($brokerId === $broker->id)>{{ $broker->name }}</option>
        @endforeach
    </select>
    <div class="flex gap-2">
        <button type="submit" class="flex-1 rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-800">Filter</button>
        @if($search !== '' || $brokerId)
        <a href="{{ route('admin.reservations', ['status' => $status]) }}" class="rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-4 py-2 text-center text-sm font-medium text-stone-600 dark:text-stone-300 transition hover:bg-stone-50 dark:hover:bg-stone-800">Clear</a>
        @endif
    </div>
</form>

@if($reservations->isEmpty())
<div class="rounded-xl border border-dashed border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 px-6 py-14 text-center">
    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-teal-50 text-teal-700 dark:bg-teal-950 dark:text-teal-300" aria-hidden="true">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
    </span>
    @if($search !== '' || $brokerId)
    <p class="mt-3 font-semibold text-stone-800 dark:text-stone-100">No reservations match your search</p>
    <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Try another code, name, or broker.</p>
    @else
    <p class="mt-3 font-semibold text-stone-800 dark:text-stone-100">No reservations here</p>
    <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Reservations made by agents and requests from buyers will appear here.</p>
    @endif
</div>
@else

{{-- Desktop table --}}
<div class="hidden overflow-hidden rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 lg:block">
    <table class="w-full text-sm">
        <thead class="border-b border-stone-200 dark:border-stone-700 bg-stone-50 dark:bg-stone-800/60">
            <tr class="text-left text-xs uppercase tracking-wide text-stone-500 dark:text-stone-400">
                <th class="px-5 py-3 font-semibold">Reservation</th>
                <th class="px-5 py-3 font-semibold">Client</th>
                <th class="px-5 py-3 font-semibold">Property and lot</th>
                <th class="px-5 py-3 font-semibold">Agent / Broker</th>
                <th class="px-5 py-3 text-right font-semibold">Total price</th>
                <th class="px-5 py-3 font-semibold">Status</th>
                <th class="px-5 py-3"><span class="sr-only">Open</span></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
            @foreach($reservations as $res)
            <tr class="align-top transition hover:bg-stone-50 dark:hover:bg-stone-800/60">
                <td class="px-5 py-3">
                    <a href="{{ route('admin.reservations.show', $res) }}" class="font-mono text-xs font-semibold text-teal-700 hover:underline dark:text-teal-400">{{ $res->reservation_code }}</a>
                    <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">{{ $res->created_at->format('M d, Y') }}</p>
                </td>
                <td class="px-5 py-3">
                    <p class="font-medium text-stone-800 dark:text-stone-100">{{ $res->client?->full_name ?? '—' }}</p>
                    <p class="text-xs text-stone-500 dark:text-stone-400">{{ $res->client?->email }}</p>
                </td>
                <td class="px-5 py-3">
                    <p class="text-stone-700 dark:text-stone-200">{{ $res->lot?->property?->name ?? 'Property removed' }}</p>
                    <p class="text-xs text-stone-500 dark:text-stone-400">{{ $res->lot?->label ?? '—' }}</p>
                </td>
                <td class="px-5 py-3">
                    <p class="text-stone-700 dark:text-stone-200">{{ $agentOf($res)?->name ?? '—' }}</p>
                    <p class="text-xs text-stone-500 dark:text-stone-400">{{ $brokerOf($res)?->name ?? '—' }}</p>
                </td>
                <td class="whitespace-nowrap px-5 py-3 text-right font-semibold text-stone-800 dark:text-stone-100">₱{{ number_format($res->total_price, 2) }}</td>
                <td class="px-5 py-3">@include('pages.admin.reservations._status-badge', ['reservation' => $res])</td>
                <td class="px-5 py-3 text-right">
                    <a href="{{ route('admin.reservations.show', $res) }}" class="whitespace-nowrap text-xs font-semibold text-teal-700 hover:underline dark:text-teal-400">View details</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Phone and tablet cards --}}
<div class="space-y-3 lg:hidden">
    @foreach($reservations as $res)
    <a href="{{ route('admin.reservations.show', $res) }}" class="block rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 p-4 transition hover:border-stone-300 dark:hover:border-stone-600">
        <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="font-mono text-xs font-semibold text-teal-700 dark:text-teal-400">{{ $res->reservation_code }}</p>
                <p class="mt-1 truncate font-medium text-stone-800 dark:text-stone-100">{{ $res->client?->full_name ?? '—' }}</p>
            </div>
            @include('pages.admin.reservations._status-badge', ['reservation' => $res])
        </div>
        <p class="mt-2 text-sm text-stone-600 dark:text-stone-300">{{ $res->lot?->property?->name ?? 'Property removed' }} · {{ $res->lot?->label ?? '—' }}</p>
        <div class="mt-2 flex items-end justify-between gap-3">
            <p class="text-xs text-stone-500 dark:text-stone-400">{{ $agentOf($res)?->name ?? '—' }} · {{ $res->created_at->format('M d, Y') }}</p>
            <p class="whitespace-nowrap font-semibold text-stone-800 dark:text-stone-100">₱{{ number_format($res->total_price, 2) }}</p>
        </div>
    </a>
    @endforeach
</div>

@if($reservations->hasPages())
<div class="mt-5">{{ $reservations->links() }}</div>
@endif
@endif
@endsection
