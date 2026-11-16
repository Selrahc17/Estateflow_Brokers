@extends('layouts.app')
@section('title', 'Properties')
@section('page-title', 'Property Listings')
@section('page-subtitle', 'Your listings and what each one still needs')

@section('content')
@php
    $statusLabel = ['available' => 'Available', 'coming_soon' => 'Coming Soon', 'sold' => 'Sold', 'hidden' => 'Hidden by admin', 'flagged' => 'Under admin review'];
    $statusTone = [
        'available' => 'bg-green-100 text-green-800',
        'coming_soon' => 'bg-sky-100 text-sky-800',
        'sold' => 'bg-stone-800 text-white',
        'hidden' => 'bg-red-100 text-red-700',
        'flagged' => 'bg-amber-100 text-amber-800',
    ];
    $field = 'rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-700 focus:outline-none focus:ring-2 focus:ring-teal-400';
@endphp

<div class="mb-5 grid grid-cols-2 gap-4 xl:grid-cols-4">
    @include('layouts.partials.stat-tile', ['label' => 'All Listings', 'value' => number_format($stats['total']), 'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'])
    @include('layouts.partials.stat-tile', ['label' => 'For Sale', 'value' => number_format($stats['available']), 'tone' => $stats['available'] ? 'green' : null, 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'])
    @include('layouts.partials.stat-tile', ['label' => 'Sold', 'value' => number_format($stats['sold']), 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'])
    <a href="{{ route('agent.inquiries.index') }}" class="block rounded-xl transition hover:opacity-90">
        @include('layouts.partials.stat-tile', ['label' => 'New Inquiries', 'value' => number_format($stats['new_inquiries']), 'tone' => $stats['new_inquiries'] ? 'amber' : null, 'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'])
    </a>
</div>

{{-- Toolbar --}}
<div class="mb-5 flex flex-col gap-2 rounded-xl border border-stone-200 bg-white p-4 lg:flex-row lg:items-center">
    <form method="GET" class="flex flex-1 flex-col gap-2 sm:flex-row">
        <label for="property-search" class="sr-only">Search listings</label>
        <div class="relative flex-1">
            <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            <input id="property-search" type="search" name="search" value="{{ $search }}" placeholder="Search name, city or province..." class="{{ $field }} w-full pl-9">
        </div>
        <label for="property-type" class="sr-only">Type</label>
        <select id="property-type" name="type" onchange="this.form.submit()" class="{{ $field }}">
            <option value="">All types</option>
            @foreach(\App\Models\Property::TYPES as $option)
            <option value="{{ $option }}" @selected($type === $option)>{{ $option }}</option>
            @endforeach
        </select>
        <label for="property-status" class="sr-only">Status</label>
        <select id="property-status" name="status" onchange="this.form.submit()" class="{{ $field }}">
            <option value="">Any status</option>
            @foreach($statusLabel as $value => $text)
            <option value="{{ $value }}" @selected($status === $value)>{{ $text }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-lg bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-900">Search</button>
    </form>
    <a href="{{ route('agent.properties.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Property
    </a>
</div>

@if($properties->isEmpty())
<div class="rounded-xl border border-stone-200 bg-white px-6 py-16 text-center">
    <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-teal-50 text-teal-700" aria-hidden="true">
        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
    </span>
    @if($search || $status || $type)
    <h2 class="mt-4 font-semibold text-stone-800">No listings match</h2>
    <p class="mt-1 text-sm text-stone-500"><a href="{{ route('agent.properties.index') }}" class="font-medium text-teal-700 hover:underline">Clear the filters</a> to see all listings.</p>
    @else
    <h2 class="mt-4 font-semibold text-stone-800">No listings yet</h2>
    <p class="mx-auto mt-1 max-w-sm text-sm text-stone-500">Add your first property. Buyers see it on the public site once it is available.</p>
    <a href="{{ route('agent.properties.create') }}" class="mt-4 inline-flex rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">Add Property</a>
    @endif
</div>
@else
<div class="grid grid-cols-1 gap-5 sm:grid-cols-2 xl:grid-cols-3">
    @foreach($properties as $property)
    @php
        $hasLots = $property->lots_count > 0;
        $unit = $property->type === 'Condominium' ? 'units' : 'lots';
        $priceFrom = $property->price ?: ($property->lowest_lot_price ?: $property->lowest_model_price);
        $checks = [
            'Photo' => filled($property->featured_image) || ! empty($property->images),
            'Price' => (bool) $priceFrom,
            'Map pin' => filled($property->latitude) && filled($property->longitude),
            'Payment terms' => $property->hasPaymentTerms(),
        ];
        $missing = array_keys(array_filter($checks, fn ($done) => ! $done));
    @endphp
    <article class="flex flex-col overflow-hidden rounded-xl border border-stone-200 bg-white transition hover:shadow-md">
        <a href="{{ route('agent.properties.show', $property) }}" class="relative block aspect-[16/10] bg-stone-100">
            @if($property->featured_image)
            <img src="{{ $property->featured_image }}" alt="" loading="lazy" class="absolute inset-0 h-full w-full object-cover">
            @else
            <span class="absolute inset-0 flex flex-col items-center justify-center gap-1 text-stone-400">
                <svg class="h-10 w-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span class="text-xs font-medium">No photo yet</span>
            </span>
            @endif
            <span class="absolute left-3 top-3 rounded-full px-2.5 py-1 text-xs font-semibold shadow-sm {{ $statusTone[$property->status] ?? 'bg-stone-100 text-stone-700' }}">{{ $statusLabel[$property->status] ?? ucfirst(str_replace('_', ' ', $property->status)) }}</span>
        </a>

        <div class="flex flex-1 flex-col p-4">
            <p class="text-xs font-medium text-stone-500">{{ $property->type }}</p>
            <h3 class="mt-0.5 font-semibold text-stone-800"><a href="{{ route('agent.properties.show', $property) }}" class="hover:text-teal-700">{{ $property->name }}</a></h3>
            <p class="mt-0.5 truncate text-sm text-stone-500">{{ collect([$property->city, $property->province])->filter()->implode(', ') ?: 'No location yet' }}</p>

            <dl class="mt-3 grid grid-cols-2 gap-3 border-t border-stone-100 pt-3 text-sm">
                <div>
                    <dt class="text-xs text-stone-500">{{ $property->price ? 'Price' : 'Starts at' }}</dt>
                    <dd class="font-semibold text-stone-800">{{ $priceFrom ? '₱' . number_format($priceFrom) : '—' }}</dd>
                </div>
                <div>
                    <dt class="text-xs text-stone-500">{{ $hasLots ? 'Available ' . $unit : 'Inquiries' }}</dt>
                    <dd class="font-semibold text-stone-800">
                        @if($hasLots)
                        {{ $property->available_lots_count }} <span class="font-normal text-stone-500">of {{ $property->lots_count }}</span>
                        @else
                        {{ $property->inquiries_count }}
                        @endif
                    </dd>
                </div>
            </dl>

            @if($hasLots)
            <div class="mt-3">
                <div class="flex h-1.5 overflow-hidden rounded-full bg-stone-100" role="img" aria-label="{{ $property->sold_lots_count }} of {{ $property->lots_count }} {{ $unit }} sold">
                    <span class="bg-teal-600" style="width: {{ round($property->sold_lots_count / $property->lots_count * 100, 1) }}%"></span>
                    <span class="bg-amber-400" style="width: {{ round(max(0, $property->lots_count - $property->sold_lots_count - $property->available_lots_count) / $property->lots_count * 100, 1) }}%"></span>
                </div>
                <p class="mt-1 text-xs text-stone-500">{{ $property->sold_lots_count }} sold · {{ max(0, $property->lots_count - $property->sold_lots_count - $property->available_lots_count) }} reserved · {{ $property->inquiries_count }} {{ $property->inquiries_count === 1 ? 'inquiry' : 'inquiries' }}</p>
            </div>
            @endif

            @if($property->status !== 'sold')
                @if($missing)
                <div class="mt-3 rounded-lg bg-amber-50 px-3 py-2 text-xs text-amber-800">
                    <span class="font-semibold">Missing:</span> {{ implode(', ', $missing) }}
                </div>
                @else
                <p class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-green-700">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                    Listing details complete
                </p>
                @endif
            @endif

            <div class="mt-auto flex flex-wrap gap-2 pt-4">
                <a href="{{ route('agent.properties.show', $property) }}" class="flex-1 rounded-lg bg-teal-700 py-2 text-center text-xs font-semibold text-white hover:bg-teal-800">View</a>
                <a href="{{ route('agent.properties.edit', $property) }}" class="flex-1 rounded-lg border border-stone-200 py-2 text-center text-xs font-semibold text-stone-700 hover:bg-stone-50">Edit</a>
                @if($hasLots)
                <a href="{{ route('agent.lots.index', ['property_id' => $property->id]) }}" class="flex-1 rounded-lg border border-stone-200 py-2 text-center text-xs font-semibold text-stone-700 hover:bg-stone-50">Lots</a>
                @endif
                <form method="POST" action="{{ route('agent.properties.destroy', $property) }}" onsubmit="return confirm('Delete {{ e(addslashes($property->name)) }}? This cannot be undone.')">
                    @csrf @method('DELETE')
                    <button type="submit" class="rounded-lg border border-red-200 px-3 py-2 text-xs font-semibold text-red-700 hover:bg-red-50" aria-label="Delete {{ $property->name }}">Delete</button>
                </form>
            </div>
            {{-- Listings with lots are sold lot by lot in Lot Availability. --}}
            @if(in_array($property->status, ['available', 'coming_soon'], true) && ! $hasLots)
            <form method="POST" action="{{ route('agent.properties.mark-sold', $property) }}" onsubmit="return confirm('Mark {{ e(addslashes($property->name)) }} as sold? It will be added to your broker\'s commission records.')" class="mt-2">
                @csrf @method('PATCH')
                <button type="submit" class="w-full rounded-lg border border-amber-300 bg-amber-50 py-2 text-center text-xs font-semibold text-amber-800 transition hover:bg-amber-100">Mark as Sold</button>
            </form>
            @endif
        </div>
    </article>
    @endforeach
</div>
@endif

@if($properties->hasPages())
<div class="mt-6">{{ $properties->links() }}</div>
@endif
@endsection
