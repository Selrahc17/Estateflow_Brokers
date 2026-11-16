@extends('layouts.app')
@section('title', 'Lot Availability')
@section('page-title', 'Lot Availability')
@section('page-subtitle', 'Lots and units of your projects, and which are still available')

@section('content')
@use('App\Models\Lot')
@php
    $view = $selected ? $view : 'list';
    $peso = fn ($amount) => '₱' . number_format((float) $amount, 0);
    $tone = [
        'available' => ['tile' => 'border-green-300 bg-green-50 text-green-900 hover:bg-green-100', 'badge' => 'bg-green-100 text-green-800', 'dot' => 'bg-green-500'],
        'reserved' => ['tile' => 'border-amber-300 bg-amber-50 text-amber-900 hover:bg-amber-100', 'badge' => 'bg-amber-100 text-amber-800', 'dot' => 'bg-amber-500'],
        'sold' => ['tile' => 'border-red-200 bg-red-50 text-red-800 hover:bg-red-100', 'badge' => 'bg-red-100 text-red-700', 'dot' => 'bg-red-500'],
    ];
    $field = 'rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-700 focus:outline-none focus:ring-2 focus:ring-teal-400';
    $query = fn (array $changes = []) => array_filter(array_merge([
        'property_id' => $selected?->id ?? '', 'view' => $view === 'list' ? 'list' : null, 'status' => $status, 'q' => $search ?: null,
    ], $changes), fn ($value) => $value !== null);
    $byBlock = $lots->groupBy(fn (Lot $lot) => $lot->block ?? '');
    // What the pop-up shows for each lot.
    $details = $lots->mapWithKeys(fn (Lot $lot) => [$lot->id => [
        'name' => $lot->lot_number,
        'project' => $lot->property?->name,
        'size' => $lot->square_meters ? rtrim(rtrim(number_format((float) $lot->square_meters, 2), '0'), '.') . ' sqm' : null,
        'price' => $lot->price ? $peso($lot->price) : null,
        'perSqm' => $lot->price_per_sqm ? $peso($lot->price_per_sqm) . ' / sqm' : null,
        // A note that only repeats the price per sqm is left out.
        'notes' => preg_match('/^\s*₱?[\d,.]+\s*(per|\/)\s*sq\.?\s*m\.?\s*$/iu', (string) $lot->description) ? null : $lot->description,
        'status' => $lot->status,
        'statusUrl' => route('agent.lots.status', $lot),
        'editUrl' => route('agent.lots.edit', $lot),
    ]]);
@endphp

@if(session('success'))
<div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700" role="status">{{ session('success') }}</div>
@endif

@if($properties->isEmpty())
<div class="rounded-xl border border-stone-200 bg-white px-6 py-16 text-center">
    <h2 class="font-semibold text-stone-800">No projects yet</h2>
    <p class="mt-1 text-sm text-stone-500">Add a property first. Then add its lots or units here.</p>
    <a href="{{ route('agent.properties.create') }}" class="mt-4 inline-flex rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Add Property</a>
</div>
@else
<div x-data="{ open: null, lots: @js($details) }" @keydown.escape.window="open = null">

{{-- Project picker --}}
<div class="mb-5 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
    <form method="GET" class="flex min-w-0 flex-1 items-center gap-2">
        @if($view === 'list')<input type="hidden" name="view" value="list">@endif
        <label for="lot-project" class="shrink-0 text-sm font-medium text-stone-600">Project</label>
        <select id="lot-project" name="property_id" onchange="this.form.submit()" class="{{ $field }} min-w-0 flex-1 lg:max-w-md">
            <option value="" @selected(! $selected)>All projects</option>
            @foreach($properties as $property)
            <option value="{{ $property->id }}" @selected($selected?->id === $property->id)>{{ $property->name }} ({{ $property->lots_count }} {{ $property->lots_count === 1 ? 'lot' : 'lots' }})</option>
            @endforeach
        </select>
    </form>
    <div class="flex items-center gap-2">
        @if($selected)
        <div class="inline-flex rounded-lg border border-stone-200 bg-white p-0.5" role="group" aria-label="Layout">
            @foreach(['grid' => 'Site plan', 'list' => 'List'] as $value => $text)
            <a href="{{ route('agent.lots.index', $query(['view' => $value === 'list' ? 'list' : null])) }}" @if($view === $value) aria-current="page" @endif
               class="rounded-md px-3 py-1.5 text-xs font-semibold {{ $view === $value ? 'bg-teal-700 text-white' : 'text-stone-600 hover:bg-stone-50' }}">{{ $text }}</a>
            @endforeach
        </div>
        @endif
        <a href="{{ route('agent.lots.create', array_filter(['property_id' => $selected?->id])) }}" class="inline-flex items-center gap-1.5 rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Lot
        </a>
    </div>
</div>

{{-- Summary --}}
<div class="mb-5 grid grid-cols-2 gap-3 lg:grid-cols-4">
    @foreach([
        ['Available', $stats['available'], 'available'],
        ['Reserved', $stats['reserved'], 'reserved'],
        ['Sold', $stats['sold'], 'sold'],
    ] as [$label, $count, $key])
    <a href="{{ route('agent.lots.index', $query(['status' => $status === $key ? null : $key])) }}" @if($status === $key) aria-current="true" @endif
       class="flex items-center gap-3 rounded-xl border bg-white p-4 transition hover:border-teal-300 {{ $status === $key ? 'border-teal-500 ring-2 ring-teal-100' : 'border-stone-200' }}">
        <span class="h-3 w-3 shrink-0 rounded-full {{ $tone[$key]['dot'] }}" aria-hidden="true"></span>
        <span>
            <span class="block text-xs font-medium text-stone-500">{{ $label }}</span>
            <span class="block text-xl font-bold text-stone-800">{{ number_format($count) }} <span class="text-sm font-normal text-stone-400">of {{ number_format($stats['total']) }}</span></span>
        </span>
    </a>
    @endforeach
    <div class="rounded-xl border border-stone-200 bg-white p-4">
        <span class="block text-xs font-medium text-stone-500">Value still available</span>
        <span class="block text-xl font-bold text-teal-700">{{ $peso($stats['available_value']) }}</span>
    </div>
</div>

<div class="overflow-hidden rounded-xl border border-stone-200 bg-white">
    {{-- Search and filters --}}
    <form method="GET" class="flex flex-col gap-2 border-b border-stone-100 p-4 sm:flex-row sm:items-center">
        <input type="hidden" name="property_id" value="{{ $selected?->id }}">
        @if($view === 'list')<input type="hidden" name="view" value="list">@endif
        @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
        <label for="lot-search" class="sr-only">Find a lot</label>
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input id="lot-search" type="search" name="q" value="{{ $search }}" placeholder="Find a lot, e.g. Blk 2 Lot 45" class="{{ $field }} w-full pl-9">
        </div>
        <button type="submit" class="rounded-lg bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-900">Find</button>
        @if($status || $search)
        <a href="{{ route('agent.lots.index', $query(['status' => null, 'q' => null])) }}" class="px-2 py-2 text-center text-sm text-stone-500 hover:text-stone-800">Clear filters</a>
        @endif
        <div class="flex items-center gap-3 text-xs text-stone-500 sm:ml-auto" aria-hidden="true">
            @foreach(Lot::STATUSES as $key => $label)
            <span class="flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full {{ $tone[$key]['dot'] }}"></span>{{ $label }}</span>
            @endforeach
        </div>
    </form>

    @if($lots->isEmpty())
    <div class="px-6 py-14 text-center">
        @if($stats['total'] === 0)
        <h2 class="font-semibold text-stone-800">{{ $selected ? "{$selected->name} has no lots yet" : 'No lots yet' }}</h2>
        <p class="mt-1 text-sm text-stone-500">Add lots for projects sold lot by lot, like a subdivision. A single house or lot doesn't need any; mark the listing itself as sold.</p>
        @else
        <h2 class="font-semibold text-stone-800">No lots match these filters</h2>
        <p class="mt-1 text-sm text-stone-500"><a href="{{ route('agent.lots.index', $query(['status' => null, 'q' => null])) }}" class="font-medium text-teal-700 hover:underline">Clear the filters</a> to see all lots.</p>
        @endif
    </div>
    @elseif($view === 'grid')
    {{-- Site plan: blocks of lot tiles --}}
    <div class="space-y-6 p-4 sm:p-5">
        @foreach($byBlock as $block => $blockLots)
        <section aria-label="{{ $block !== '' ? "Block {$block}" : 'Other lots' }}">
            <div class="mb-2 flex items-baseline justify-between">
                <h3 class="text-sm font-bold text-stone-800">{{ $block !== '' ? "Block {$block}" : 'Lots' }}</h3>
                <p class="text-xs text-stone-500">{{ $blockLots->where('status', 'available')->count() }} of {{ $blockLots->count() }} available</p>
            </div>
            <div class="grid grid-cols-[repeat(auto-fill,minmax(4.5rem,1fr))] gap-2">
                @foreach($blockLots as $lot)
                <button type="button" @click="open = {{ $lot->id }}"
                        class="rounded-lg border px-1.5 py-2 text-center transition focus:outline-none focus:ring-2 focus:ring-teal-400 {{ $tone[$lot->status]['tile'] ?? 'border-stone-200' }}"
                        aria-label="{{ $lot->lot_number }}, {{ Lot::STATUSES[$lot->status] ?? $lot->status }}{{ $lot->price ? ', ' . $peso($lot->price) : '' }}">
                    <span class="block text-sm font-bold leading-tight">{{ $block !== '' ? $lot->short_number : $lot->lot_number }}</span>
                    @if($lot->square_meters)<span class="block text-[11px] leading-tight opacity-80">{{ rtrim(rtrim(number_format((float) $lot->square_meters, 2), '0'), '.') }} sqm</span>@endif
                </button>
                @endforeach
            </div>
        </section>
        @endforeach
        <p class="text-xs text-stone-500">Tap a lot to see its price or change its status.</p>
    </div>
    @else
    {{-- List --}}
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                <tr>
                    <th class="px-4 py-3">Lot</th>
                    @unless($selected)<th class="px-4 py-3">Project</th>@endunless
                    <th class="px-4 py-3 text-right">Size</th>
                    <th class="px-4 py-3 text-right">Price</th>
                    <th class="px-4 py-3 text-right">Per sqm</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3"><span class="sr-only">Actions</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach($lots as $lot)
                <tr class="hover:bg-stone-50/70">
                    <td class="whitespace-nowrap px-4 py-3 font-semibold text-stone-800">{{ $lot->lot_number }}</td>
                    @unless($selected)<td class="px-4 py-3 text-stone-600">{{ $lot->property?->name }}</td>@endunless
                    <td class="whitespace-nowrap px-4 py-3 text-right text-stone-600">{{ $lot->square_meters ? rtrim(rtrim(number_format((float) $lot->square_meters, 2), '0'), '.') . ' sqm' : '—' }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-right font-semibold text-stone-800">{{ $lot->price ? $peso($lot->price) : '—' }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-right text-stone-500">{{ $lot->price_per_sqm ? $peso($lot->price_per_sqm) : '—' }}</td>
                    <td class="px-4 py-3">
                        <form method="POST" action="{{ route('agent.lots.status', $lot) }}" @if($lot->status !== 'sold') onsubmit="if (this.status.value === 'sold' && ! confirm('Mark {{ e(addslashes($lot->lot_number)) }} as sold? It will be added to your broker\'s commission records.')) { this.status.value = '{{ $lot->status }}'; return false; } return true;" @endif>
                            @csrf @method('PATCH')
                            <label for="status-{{ $lot->id }}" class="sr-only">Status of {{ $lot->lot_number }}</label>
                            <select id="status-{{ $lot->id }}" name="status" onchange="this.form.requestSubmit()" class="rounded-full border-0 px-2.5 py-1 text-xs font-semibold focus:ring-2 focus:ring-teal-400 {{ $tone[$lot->status]['badge'] ?? '' }}">
                                @foreach(Lot::STATUSES as $value => $label)
                                <option value="{{ $value }}" @selected($lot->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="{{ route('agent.lots.edit', $lot) }}" class="text-xs font-semibold text-teal-700 hover:underline">Edit</a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endif
</div>

{{-- Lot details pop-up --}}
<template x-if="open !== null && lots[open]">
    <div class="fixed inset-0 z-50 flex items-end justify-center bg-stone-900/40 p-4 sm:items-center" @click.self="open = null" role="dialog" aria-modal="true" :aria-label="lots[open].name">
        <div class="w-full max-w-sm rounded-2xl bg-white p-5 shadow-xl">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="text-xs text-stone-500" x-text="lots[open].project"></p>
                    <h3 class="text-lg font-bold text-stone-800" x-text="lots[open].name"></h3>
                </div>
                <button type="button" @click="open = null" class="rounded-lg p-1 text-stone-400 hover:bg-stone-100 hover:text-stone-600" aria-label="Close">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <dl class="mt-4 grid grid-cols-2 gap-3 text-sm">
                <div class="rounded-lg bg-stone-50 p-3"><dt class="text-xs text-stone-500">Size</dt><dd class="font-semibold text-stone-800" x-text="lots[open].size ?? '—'"></dd></div>
                <div class="rounded-lg bg-stone-50 p-3"><dt class="text-xs text-stone-500">Price</dt><dd class="font-semibold text-stone-800" x-text="lots[open].price ?? '—'"></dd><dd class="text-xs text-stone-500" x-text="lots[open].perSqm"></dd></div>
            </dl>
            <p class="mt-3 text-xs text-stone-500" x-show="lots[open].notes" x-text="lots[open].notes"></p>

            <p class="mt-4 mb-2 text-xs font-semibold uppercase tracking-wide text-stone-500">Status</p>
            <div class="grid grid-cols-3 gap-2">
                @foreach(Lot::STATUSES as $value => $label)
                <form method="POST" :action="lots[open].statusUrl" @if($value === 'sold') onsubmit="return confirm('Mark this lot as sold? It will be added to your broker\'s commission records.')" @endif>
                    @csrf @method('PATCH')
                    <input type="hidden" name="status" value="{{ $value }}">
                    <button type="submit" :disabled="lots[open].status === '{{ $value }}'"
                            :class="lots[open].status === '{{ $value }}' ? '{{ $tone[$value]['badge'] }} ring-2 ring-offset-1 ring-current cursor-default' : 'border border-stone-200 text-stone-700 hover:bg-stone-50'"
                            class="w-full rounded-lg py-2 text-xs font-semibold transition">{{ $label }}</button>
                </form>
                @endforeach
            </div>
            <a :href="lots[open].editUrl" class="mt-4 block rounded-lg border border-stone-200 py-2 text-center text-sm font-semibold text-stone-700 hover:bg-stone-50">Edit lot details</a>
        </div>
    </div>
</template>
</div>
@endif
@endsection
