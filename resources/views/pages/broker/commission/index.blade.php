@extends('layouts.broker')

@section('title', 'Commission Management')
@section('page-title', 'Commission Management')
@section('page-subtitle', 'Sales by your agents and the commission on each')

@section('content')
@php
    $peso = fn ($amount) => $amount === null ? '—' : '₱' . number_format((float) $amount, 2);
    $statusTone = [
        'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
        'transferred' => 'bg-sky-100 text-sky-800 dark:bg-sky-950 dark:text-sky-300',
        'received' => 'bg-green-100 text-green-800 dark:bg-green-950 dark:text-green-300',
    ];
    $field = 'w-full rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 px-3 py-2 text-sm text-stone-700 dark:text-stone-200 focus:outline-none focus:ring-2 focus:ring-teal-400';
@endphp

@if(session('success'))
<div class="mb-5 rounded-lg border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-950 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
@endif

<div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
        <h2 class="text-xl font-semibold text-stone-800 dark:text-stone-100">Commission Records</h2>
        <p class="text-sm text-stone-500 dark:text-stone-400">A sale is added here when an agent marks a listing or lot as Sold. You can also add one yourself.</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <a href="{{ route('broker.commissions.export', request()->only('agent', 'status', 'search')) }}" class="inline-flex items-center justify-center gap-2 rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-4 py-2.5 text-sm font-medium text-stone-700 dark:text-stone-200 shadow-sm transition hover:bg-stone-50 dark:hover:bg-stone-800">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export to Excel
        </a>
        <a href="{{ route('broker.commissions.create') }}" class="inline-flex items-center justify-center rounded-lg bg-[var(--color-primary)] px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-[var(--color-primary-dark)]">
            + Record Sale
        </a>
    </div>
</div>

<div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
    @foreach([
        ['Sales', number_format($totals->sales ?? 0), null],
        ['Total Selling Price', $peso($totals->selling ?? 0), null],
        ['Agent Commission', $peso($totals->agent ?? 0), 'amber'],
        ['Broker Commission', $peso($totals->broker ?? 0), 'green'],
    ] as [$label, $value, $tone])
        @include('layouts.partials.stat-tile', compact('label', 'value', 'tone'))
    @endforeach
</div>

<form method="GET" class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-[1fr_200px_180px_auto]">
    <label class="sr-only" for="filter-search">Search property</label>
    <input id="filter-search" type="search" name="search" value="{{ request('search') }}" placeholder="Search property or project..." class="{{ $field }}">
    <label class="sr-only" for="filter-agent">Agent</label>
    <select id="filter-agent" name="agent" class="{{ $field }}">
        <option value="">All agents</option>
        @foreach($agents as $agent)
        <option value="{{ $agent->id }}" @selected((string) request('agent') === (string) $agent->id)>{{ $agent->name }}</option>
        @endforeach
    </select>
    <label class="sr-only" for="filter-status">Status</label>
    <select id="filter-status" name="status" class="{{ $field }}">
        <option value="">All statuses</option>
        @foreach(\App\Models\Sale::STATUSES as $value => $label)
        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <div class="flex gap-2">
        <button type="submit" class="rounded-lg bg-stone-800 dark:bg-stone-700 px-4 py-2 text-sm font-medium text-white hover:bg-stone-900 dark:hover:bg-stone-600">Filter</button>
        @if(request()->hasAny(['search', 'agent', 'status']))
        <a href="{{ route('broker.commissions.index') }}" class="rounded-lg px-3 py-2 text-sm text-stone-500 dark:text-stone-400 hover:text-stone-700 dark:hover:text-stone-200">Clear</a>
        @endif
    </div>
</form>

<div class="mt-4 overflow-hidden rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 shadow-sm">
    <div class="overflow-x-auto">
        <table class="w-full min-w-[1040px] divide-y divide-stone-200 dark:divide-stone-700 text-sm">
            <thead class="bg-stone-50 dark:bg-stone-800/60">
                <tr class="text-left text-stone-500 dark:text-stone-400">
                    <th class="whitespace-nowrap px-4 py-3 font-medium">Agent</th>
                    <th class="min-w-[260px] whitespace-nowrap px-4 py-3 font-medium">Property / Project</th>
                    <th class="whitespace-nowrap px-4 py-3 font-medium">Date of Sale</th>
                    <th class="whitespace-nowrap px-4 py-3 text-right font-medium">Selling Price</th>
                    <th class="whitespace-nowrap px-4 py-3 text-right font-medium">Agent Commission</th>
                    <th class="whitespace-nowrap px-4 py-3 text-right font-medium">Broker Commission</th>
                    <th class="whitespace-nowrap px-4 py-3 font-medium">Status</th>
                    <th class="whitespace-nowrap px-4 py-3 font-medium"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                @forelse($sales as $sale)
                <tr class="transition hover:bg-stone-50 dark:hover:bg-stone-800/60">
                    <td class="whitespace-nowrap px-4 py-3 font-medium text-stone-800 dark:text-stone-100">{{ $sale->agent?->name ?? 'Removed agent' }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-11 w-14 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-stone-100 dark:bg-stone-800">
                                @if($sale->photo_url)
                                <img src="{{ $sale->photo_url }}" alt="" class="h-full w-full object-cover" loading="lazy">
                                @else
                                <svg class="h-5 w-5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                                @endif
                            </div>
                            <div class="min-w-0">
                                <p class="font-medium text-stone-800 dark:text-stone-100">{{ $sale->item_name }}</p>
                                <p class="text-xs text-stone-500 dark:text-stone-400">{{ $sale->source === 'auto' ? 'Marked sold by agent' : 'Added by you' }}{{ $sale->show_status_to_agent ? ' · Status shown to agent' : '' }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-stone-600 dark:text-stone-300">{{ $sale->sold_at?->format('M j, Y') }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-right text-stone-700 dark:text-stone-200">{{ $peso($sale->selling_price) }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-right text-stone-700 dark:text-stone-200">{{ $peso($sale->agent_commission) }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-right text-stone-700 dark:text-stone-200">{{ $peso($sale->broker_commission) }}</td>
                    <td class="whitespace-nowrap px-4 py-3">
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusTone[$sale->status] ?? '' }}">{{ $sale->status_label }}</span>
                    </td>
                    <td class="whitespace-nowrap px-4 py-3 text-right">
                        <a href="{{ route('broker.commissions.edit', $sale) }}" class="text-sm font-medium text-teal-700 dark:text-teal-400 hover:underline">Edit</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-sm text-stone-500 dark:text-stone-400">
                        @if(request()->hasAny(['search', 'agent', 'status']))
                            No sales match these filters.
                        @else
                            No sales yet. When an agent marks a listing or lot as Sold, it appears here.
                        @endif
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-4">{{ $sales->links() }}</div>
@endsection
