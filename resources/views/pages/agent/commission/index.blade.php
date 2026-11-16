@extends('layouts.app')
@section('title', 'My Sales')
@section('page-title', 'My Sales')
@section('page-subtitle', 'Properties you sold and their commission status')

@section('content')
@php
    $peso = fn ($amount) => '₱' . number_format((float) $amount, 0);
    // Totals in the millions are shortened (₱3.8M) so they fit on a phone.
    $short = fn ($amount) => (float) $amount >= 1000000 ? '₱' . rtrim(rtrim(number_format((float) $amount / 1000000, 2), '0'), '.') . 'M' : $peso($amount);
    $statusInfo = [
        'pending' => ['Pending', 'bg-amber-100 text-amber-800', 'Your broker has not released this commission yet.'],
        'transferred' => ['Transferred', 'bg-sky-100 text-sky-800', 'Your broker has sent this commission.'],
        'received' => ['Received', 'bg-green-100 text-green-800', 'This commission has been received.'],
    ];
    $field = 'rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-700 focus:outline-none focus:ring-2 focus:ring-teal-400';
@endphp

{{-- Summary --}}
<div class="mb-5 grid grid-cols-2 gap-4 xl:grid-cols-4">
    @foreach([
        [$year ? "Sales in {$year}" : 'All Sales', number_format($stats['sales']), null, 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        [$year ? "Value Sold in {$year}" : 'Total Value Sold', $short($stats['value']), 'green', 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Sales This Year', number_format($stats['this_year']), null, 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ['Commission Received', number_format($stats['received']), $stats['received'] ? 'green' : null, 'M5 13l4 4L19 7'],
    ] as [$label, $value, $tone, $icon])
        @include('layouts.partials.stat-tile', compact('label', 'value', 'tone', 'icon'))
    @endforeach
</div>

<div class="grid grid-cols-1 gap-5 xl:grid-cols-[1fr_300px]">
    <div class="overflow-hidden rounded-xl border border-stone-200 bg-white">
        <div class="flex flex-col gap-3 border-b border-stone-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="font-semibold text-stone-800">Sold Properties</h2>
                <p class="text-xs text-stone-500">Newest first</p>
            </div>
            @if($years->count() > 1)
            <form method="GET" class="flex items-center gap-2">
                <label for="sales-year" class="text-sm text-stone-600">Year</label>
                <select id="sales-year" name="year" onchange="this.form.submit()" class="{{ $field }}">
                    <option value="">All years</option>
                    @foreach($years as $option)
                    <option value="{{ $option }}" @selected($year === $option)>{{ $option }}</option>
                    @endforeach
                </select>
            </form>
            @endif
        </div>

        @if($sales->isEmpty())
        <div class="px-6 py-16 text-center">
            <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-teal-50 text-teal-700" aria-hidden="true">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            </span>
            <h3 class="mt-4 font-semibold text-stone-800">No sales yet</h3>
            <p class="mx-auto mt-1 max-w-sm text-sm text-stone-500">When you close a sale, use <span class="font-medium text-stone-700">Mark as Sold</span> on the listing or lot. It shows up here and in your broker's records.</p>
            <div class="mt-5 flex justify-center gap-2">
                <a href="{{ route('agent.properties.index') }}" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">My Properties</a>
                <a href="{{ route('agent.lots.index') }}" class="rounded-lg border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50">Lot Availability</a>
            </div>
        </div>
        @else
        <ul class="divide-y divide-stone-100">
            @foreach($sales as $sale)
            @php([$statusLabel, $statusClass, $statusHelp] = $statusInfo[$sale->status] ?? [ucfirst((string) $sale->status), 'bg-stone-100 text-stone-700', ''])
            <li class="flex flex-col gap-4 p-4 sm:flex-row sm:items-center sm:px-5">
                <div class="flex min-w-0 flex-1 items-center gap-4">
                    <div class="flex h-16 w-20 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-stone-100">
                        @if($sale->photo_url)
                        <img src="{{ $sale->photo_url }}" alt="" class="h-full w-full object-cover" loading="lazy">
                        @else
                        <svg class="h-6 w-6 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                        @endif
                    </div>
                    <div class="min-w-0">
                        <p class="truncate font-semibold text-stone-800">{{ $sale->property?->name ?? 'Removed listing' }}</p>
                        @if($sale->lot)<p class="text-sm text-stone-600">{{ $sale->lot->lot_number }}</p>@endif
                        <p class="text-xs text-stone-500">Sold {{ $sale->sold_at?->format('F j, Y') }}</p>
                    </div>
                </div>
                <div class="flex items-center justify-between gap-6 sm:justify-end">
                    <div class="sm:text-right">
                        <p class="text-xs text-stone-500">Selling price</p>
                        <p class="font-bold text-stone-800">{{ $sale->selling_price !== null ? $peso($sale->selling_price) : '—' }}</p>
                    </div>
                    <div class="w-36 text-right">
                        <p class="text-xs text-stone-500">Commission</p>
                        @if($sale->show_status_to_agent)
                        <span class="mt-0.5 inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}" title="{{ $statusHelp }}">{{ $statusLabel }}</span>
                        @else
                        <span class="mt-0.5 inline-flex rounded-full bg-stone-100 px-2.5 py-1 text-xs font-medium text-stone-500" title="Your broker has not shared the status of this sale.">Not shared yet</span>
                        @endif
                    </div>
                </div>
            </li>
            @endforeach
        </ul>
        @if($sales->hasPages())
        <div class="border-t border-stone-100 px-5 py-3">{{ $sales->links() }}</div>
        @endif
        @endif
    </div>

    {{-- How it works --}}
    <aside class="h-fit space-y-4 rounded-xl border border-stone-200 bg-white p-5 text-sm">
        <div>
            <h3 class="font-semibold text-stone-800">How sales get here</h3>
            <p class="mt-1 text-stone-600">Use <span class="font-medium">Mark as Sold</span> on a listing in Properties, or on a lot in Lot Availability. The sale is added here and to your broker's commission records.</p>
        </div>
        <div class="border-t border-stone-100 pt-4">
            <h3 class="font-semibold text-stone-800">Commission status</h3>
            <ul class="mt-2 space-y-2">
                @foreach($statusInfo as [$label, $class, $help])
                <li class="flex items-start gap-2">
                    <span class="mt-0.5 shrink-0 rounded-full px-2 py-0.5 text-[11px] font-semibold {{ $class }}">{{ $label }}</span>
                    <span class="text-xs text-stone-600">{{ $help }}</span>
                </li>
                @endforeach
            </ul>
            <p class="mt-3 text-xs text-stone-500">Your broker manages commissions and chooses which statuses to share. Commission amounts are kept private.</p>
        </div>
    </aside>
</div>
@endsection
