@extends('layouts.app')
@section('title', 'Reports')
@section('page-title', 'Reports')
@section('page-subtitle', 'How your buyers moved from inquiry to sale')

@section('content')
@php
    $peso = fn (float $amount) => $amount >= 1_000_000 ? '₱' . rtrim(rtrim(number_format($amount / 1_000_000, 2), '0'), '.') . 'M'
        : ($amount >= 1_000 ? '₱' . number_format($amount / 1_000) . 'K' : '₱' . number_format($amount));
    $maxValue = max(1, $months->max('value'));
    $maxFunnel = max(1, max($funnel));
    $hasActivity = $months->sum(fn ($month) => $month['inquiries'] + $month['viewings'] + $month['reservations'] + $month['sales']) > 0;
@endphp

<div class="mb-5 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
    <p class="text-sm text-stone-500">{{ $year === now()->year ? 'January to today, ' . $year : 'January to December, ' . $year }}</p>
    <form method="GET" class="flex items-center gap-2">
        <label for="report-year" class="text-sm font-medium text-stone-600">Year</label>
        <select id="report-year" name="year" onchange="this.form.submit()" class="rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-700 focus:outline-none focus:ring-2 focus:ring-teal-400">
            @foreach($years as $option)
            <option value="{{ $option }}" @selected($option === $year)>{{ $option }}</option>
            @endforeach
        </select>
        <noscript><button type="submit" class="rounded-lg bg-stone-800 px-3 py-2 text-sm text-white">Show</button></noscript>
    </form>
</div>

<div class="mb-5 grid grid-cols-2 gap-4 xl:grid-cols-4">
    @include('layouts.partials.stat-tile', ['label' => 'Sales', 'value' => number_format($stats['sales']), 'tone' => $stats['sales'] ? 'green' : null, 'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'])
    @include('layouts.partials.stat-tile', ['label' => 'Sales Value', 'value' => $peso($stats['value']), 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'])
    @include('layouts.partials.stat-tile', ['label' => 'Inquiries', 'value' => number_format($stats['inquiries']), 'icon' => 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'])
    @include('layouts.partials.stat-tile', ['label' => 'Inquiry to Sale', 'value' => $stats['conversion'] === null ? '—' : $stats['conversion'] . '%', 'icon' => 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'])
</div>

@if($stats['unanswered'])
<a href="{{ route('agent.inquiries.index', ['status' => 'new']) }}" class="mb-5 flex items-center justify-between gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800 hover:bg-amber-100">
    <span><span class="font-semibold">{{ $stats['unanswered'] }} {{ $stats['unanswered'] === 1 ? 'inquiry' : 'inquiries' }}</span> from {{ $year }} still {{ $stats['unanswered'] === 1 ? 'has' : 'have' }} no reply.</span>
    <span class="shrink-0 font-semibold">Reply now →</span>
</a>
@endif

<div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    {{-- Sales value by month --}}
    <section class="rounded-xl border border-stone-200 bg-white p-5 xl:col-span-2" aria-labelledby="sales-chart-title">
        <div class="flex items-baseline justify-between gap-3">
            <h2 id="sales-chart-title" class="font-semibold text-stone-800">Sales value by month</h2>
            <p class="text-sm text-stone-500">{{ $stats['sales'] }} {{ $stats['sales'] === 1 ? 'sale' : 'sales' }} · ₱{{ number_format($stats['value']) }}</p>
        </div>
        @if($stats['sales'])
        <div class="mt-6 flex h-48 items-end gap-0.5 border-b border-stone-200 sm:gap-1.5" role="img" aria-label="Sales value per month in {{ $year }}. The table below lists every month.">
            @foreach($months as $month)
            <div class="group relative flex h-full flex-1 flex-col justify-end">
                @if($month['value'] > 0)
                <div class="w-full rounded-t bg-teal-600 transition group-hover:bg-teal-700" style="height: {{ max(3, round($month['value'] / $maxValue * 100, 1)) }}%"></div>
                <div class="pointer-events-none absolute bottom-full left-1/2 z-10 mb-1 hidden -translate-x-1/2 whitespace-nowrap rounded-lg bg-stone-900 px-2.5 py-1.5 text-xs text-white shadow-lg group-hover:block">
                    <span class="font-semibold">{{ $month['label'] }}</span> · {{ $month['sales'] }} {{ $month['sales'] === 1 ? 'sale' : 'sales' }} · ₱{{ number_format($month['value']) }}
                </div>
                @endif
            </div>
            @endforeach
        </div>
        <div class="mt-1.5 flex gap-0.5 sm:gap-1.5">
            @foreach($months as $month)
            <div class="flex-1 text-center">
                <p class="text-[10px] font-medium text-stone-500 sm:text-xs">{{ $month['label'] }}</p>
                @if($month['value'] > 0)<p class="hidden text-[11px] text-stone-600 md:block">{{ $peso($month['value']) }}</p>@endif
            </div>
            @endforeach
        </div>
        @else
        <div class="mt-6 rounded-lg bg-stone-50 px-4 py-10 text-center text-sm text-stone-500">
            No sales recorded in {{ $year }}. Sales appear here when a lot or listing is marked as sold.
        </div>
        @endif
    </section>

    {{-- Inquiry to sale --}}
    <section class="rounded-xl border border-stone-200 bg-white p-5" aria-labelledby="funnel-title">
        <h2 id="funnel-title" class="font-semibold text-stone-800">From inquiry to sale</h2>
        <p class="mt-0.5 text-xs text-stone-500">Buyers who reached each step in {{ $year }}</p>
        <dl class="mt-5 space-y-4">
            @foreach($funnel as $step => $count)
            <div>
                <div class="flex items-baseline justify-between text-sm">
                    <dt class="font-medium text-stone-700">{{ $step }}</dt>
                    <dd class="font-semibold text-stone-800">{{ number_format($count) }}</dd>
                </div>
                <div class="mt-1.5 h-2 rounded-full bg-stone-100">
                    <div class="h-2 rounded-full bg-teal-600" style="width: {{ $count ? max(2, round($count / $maxFunnel * 100, 1)) : 0 }}%"></div>
                </div>
            </div>
            @endforeach
        </dl>
        <p class="mt-5 border-t border-stone-100 pt-3 text-xs text-stone-500">Viewings count confirmed and done visits. Reservations leave out cancelled and unanswered requests.</p>
    </section>

    {{-- Month by month --}}
    <section class="overflow-hidden rounded-xl border border-stone-200 bg-white xl:col-span-2" aria-labelledby="months-title">
        <h2 id="months-title" class="px-5 pt-5 font-semibold text-stone-800">Month by month</h2>
        @if($hasActivity)
        <div class="mt-3 overflow-x-auto">
            <table class="w-full min-w-[520px] text-sm">
                <thead class="bg-stone-50 text-xs uppercase tracking-wide text-stone-500">
                    <tr>
                        <th scope="col" class="px-5 py-2.5 text-left font-semibold">Month</th>
                        <th scope="col" class="px-3 py-2.5 text-right font-semibold">Inquiries</th>
                        <th scope="col" class="px-3 py-2.5 text-right font-semibold">Viewings</th>
                        <th scope="col" class="px-3 py-2.5 text-right font-semibold">Reservations</th>
                        <th scope="col" class="px-3 py-2.5 text-right font-semibold">Sales</th>
                        <th scope="col" class="px-5 py-2.5 text-right font-semibold">Sales Value</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach($months->reject(fn ($month) => $month['future']) as $month)
                    <tr class="text-stone-700">
                        <th scope="row" class="px-5 py-2.5 text-left font-medium text-stone-800">{{ $month['label'] }}</th>
                        @foreach(['inquiries', 'viewings', 'reservations', 'sales'] as $key)
                        <td class="px-3 py-2.5 text-right tabular-nums {{ $month[$key] ? '' : 'text-stone-400' }}">{{ $month[$key] ?: '–' }}</td>
                        @endforeach
                        <td class="px-5 py-2.5 text-right tabular-nums {{ $month['value'] ? 'font-semibold text-stone-800' : 'text-stone-400' }}">{{ $month['value'] ? '₱' . number_format($month['value']) : '–' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t border-stone-200 font-semibold text-stone-800">
                    <tr>
                        <th scope="row" class="px-5 py-2.5 text-left">Total</th>
                        @foreach(['inquiries', 'viewings', 'reservations', 'sales'] as $key)
                        <td class="px-3 py-2.5 text-right tabular-nums">{{ number_format($months->sum($key)) }}</td>
                        @endforeach
                        <td class="px-5 py-2.5 text-right tabular-nums">₱{{ number_format($stats['value']) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
        @else
        <p class="px-5 pb-8 pt-4 text-sm text-stone-500">Nothing happened in {{ $year }} yet.</p>
        @endif
    </section>

    {{-- Listings buyers asked about --}}
    <section class="rounded-xl border border-stone-200 bg-white p-5" aria-labelledby="top-listings-title">
        <h2 id="top-listings-title" class="font-semibold text-stone-800">Most asked-about listings</h2>
        @forelse($topListings as $index => $listing)
        <a href="{{ route('agent.properties.show', $listing) }}" class="mt-3 flex items-center gap-3 rounded-lg px-2 py-2 hover:bg-stone-50">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-teal-50 text-xs font-bold text-teal-800">{{ $index + 1 }}</span>
            <span class="min-w-0 flex-1">
                <span class="block truncate text-sm font-medium text-stone-800">{{ $listing->name }}</span>
                <span class="block text-xs text-stone-500">{{ $listing->inquiries_count }} {{ $listing->inquiries_count === 1 ? 'inquiry' : 'inquiries' }} · {{ $listing->viewings_count }} {{ $listing->viewings_count === 1 ? 'viewing' : 'viewings' }}</span>
            </span>
            @if($listing->status === 'sold')<span class="rounded-full bg-stone-100 px-2 py-0.5 text-xs font-semibold text-stone-600">Sold</span>@endif
        </a>
        @empty
        <p class="mt-3 text-sm text-stone-500">No inquiries in {{ $year }} yet.</p>
        @endforelse
    </section>
</div>
@endsection
