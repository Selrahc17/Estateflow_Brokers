@extends('layouts.broker')
@section('title', 'Agent Performance')
@section('page-title', 'Agent Performance')
@section('page-subtitle', 'Sales ranking and activity of your agents')

@section('content')
@php
    $peso = fn ($amount) => '₱' . number_format((float) $amount, 2);
    $field = 'rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 px-3 py-2 text-sm text-stone-700 dark:text-stone-200 focus:outline-none focus:ring-2 focus:ring-teal-400';
    $medal = [1 => 'bg-amber-400 text-amber-950', 2 => 'bg-stone-300 text-stone-800', 3 => 'bg-orange-300 text-orange-950'];
    $months = $report->months();
    $isCurrentYear = $report->year === now()->year;
    $teamTotals = [
        'Sales' => number_format($ranking->sum('sales')),
        'Sales Value' => $peso($ranking->sum('sales_value')),
        'Inquiries' => number_format($ranking->sum('inquiries')),
        'Viewings' => number_format($ranking->sum('viewings')),
    ];
    $monthlyRows = $ranking->map(fn ($row) => [
        'name' => $row->agent->name,
        'counts' => collect($monthly)->map(fn ($byAgent) => $months->map(fn ($month) => $byAgent[$row->agent->id][$month->format('Y-m')] ?? 0)->all())->all(),
    ])->values();
@endphp

<div class="mb-6 flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
    <div>
        <h2 class="text-xl font-semibold text-stone-800 dark:text-stone-100">Sales Ranking · {{ $report->label() }}</h2>
        <p class="text-sm text-stone-500 dark:text-stone-400">
            Ranked by number of sales. Rankings start fresh every year{{ $isCurrentYear ? '' : ', and this is a past year kept for your records' }}.
        </p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <label for="year" class="sr-only">Year</label>
            <select id="year" name="year" onchange="this.form.submit()" class="{{ $field }}">
                @foreach($years as $year)
                <option value="{{ $year }}" @selected($year === $report->year)>{{ $year }}</option>
                @endforeach
            </select>
            <label for="quarter" class="sr-only">Period</label>
            <select id="quarter" name="quarter" onchange="this.form.submit()" class="{{ $field }}">
                <option value="">Whole year</option>
                @foreach([1 => 'Q1 (Jan–Mar)', 2 => 'Q2 (Apr–Jun)', 3 => 'Q3 (Jul–Sep)', 4 => 'Q4 (Oct–Dec)'] as $quarter => $text)
                <option value="{{ $quarter }}" @selected($quarter === $report->quarter)>{{ $text }}</option>
                @endforeach
            </select>
            <noscript><button type="submit" class="{{ $field }}">Show</button></noscript>
        </form>
        <a href="{{ route('broker.performance.export', array_filter(['year' => $report->year, 'quarter' => $report->quarter])) }}" class="inline-flex items-center gap-2 rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-4 py-2 text-sm font-medium text-stone-700 dark:text-stone-200 shadow-sm transition hover:bg-stone-50 dark:hover:bg-stone-800">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Export to Excel
        </a>
    </div>
</div>

{{-- Top agent of the period --}}
<div class="mb-6 rounded-xl border border-amber-200 dark:border-amber-900 bg-gradient-to-r from-amber-50 to-white dark:from-amber-950/60 dark:to-stone-900 p-5">
    @if($topAgents->isNotEmpty())
    <div class="flex items-center gap-4">
        <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full bg-amber-400 text-amber-950" aria-hidden="true">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
        </span>
        <div class="min-w-0">
            <p class="text-xs font-semibold uppercase tracking-widest text-amber-800 dark:text-amber-300">Top Agent{{ $topAgents->count() > 1 ? 's' : '' }} · {{ $report->label() }}</p>
            <p class="text-lg font-bold text-stone-800 dark:text-stone-100">{{ $topAgents->map(fn ($row) => $row->agent->name)->join(', ', ' and ') }}</p>
            <p class="text-sm text-stone-600 dark:text-stone-300">{{ $topAgents->first()->sales }} {{ $topAgents->first()->sales === 1 ? 'sale' : 'sales' }} · {{ $peso($topAgents->first()->sales_value) }}{{ $topAgents->count() > 1 ? ' each' : '' }}</p>
        </div>
    </div>
    @else
    <p class="text-sm text-stone-600 dark:text-stone-300">No sales recorded for {{ $report->label() }} yet. The top agent appears here once a sale is recorded.</p>
    @endif
</div>

<div class="mb-6 grid grid-cols-2 gap-4 xl:grid-cols-4">
    @foreach($teamTotals as $label => $value)
        @include('layouts.partials.stat-tile', ['label' => "Team {$label}", 'value' => $value])
    @endforeach
</div>

{{-- Ranking --}}
<div class="mb-6 overflow-hidden rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900">
    <div class="flex items-center justify-between border-b border-stone-100 dark:border-stone-800 px-5 py-4">
        <h3 class="font-semibold text-stone-800 dark:text-stone-100">Agent Ranking</h3>
        <span class="text-xs text-teal-700 dark:text-teal-300">{{ $ranking->count() }} Agent{{ $ranking->count() !== 1 ? 's' : '' }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[860px] text-sm">
            <thead class="border-b border-stone-100 dark:border-stone-800 bg-stone-50 dark:bg-stone-800/60">
                <tr class="text-left text-stone-500 dark:text-stone-400">
                    <th class="px-5 py-3 font-medium">Rank</th>
                    <th class="px-5 py-3 font-medium">Agent</th>
                    <th class="px-5 py-3 font-medium">Sales</th>
                    <th class="px-5 py-3 font-medium">Sales Value</th>
                    <th class="px-5 py-3 font-medium">Listings Added</th>
                    <th class="px-5 py-3 font-medium">Inquiries</th>
                    <th class="px-5 py-3 font-medium">Viewings</th>
                    <th class="px-5 py-3 font-medium">Commission</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                @forelse($ranking as $row)
                <tr class="transition hover:bg-stone-50 dark:hover:bg-stone-800 {{ $row->rank === 1 ? 'bg-amber-50/60 dark:bg-amber-950/30' : '' }}">
                    <td class="px-5 py-4">
                        @if($row->rank)
                        <span class="inline-flex h-8 min-w-8 items-center justify-center rounded-full px-2 text-sm font-bold {{ $medal[$row->rank] ?? 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300' }}">#{{ $row->rank }}</span>
                        @else
                        <span class="text-stone-400" title="No sales in this period">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-4">
                        <p class="font-medium text-stone-700 dark:text-stone-200">{{ $row->agent->name }}</p>
                        <p class="text-xs text-stone-500 dark:text-stone-400">{{ $row->agent->email }}{{ $row->agent->is_active ? '' : ' · inactive' }}</p>
                    </td>
                    <td class="px-5 py-4 text-base font-bold text-green-700 dark:text-green-300">{{ $row->sales }}</td>
                    <td class="whitespace-nowrap px-5 py-4 text-stone-700 dark:text-stone-200">{{ $peso($row->sales_value) }}</td>
                    <td class="px-5 py-4 text-stone-700 dark:text-stone-200">{{ $row->listings }}</td>
                    <td class="px-5 py-4 font-semibold text-blue-600 dark:text-blue-300">{{ $row->inquiries }}</td>
                    <td class="px-5 py-4 font-semibold text-teal-700 dark:text-teal-300">{{ $row->viewings }}</td>
                    <td class="whitespace-nowrap px-5 py-4">
                        <p class="font-semibold text-stone-700 dark:text-stone-200">{{ $peso($row->commission_received) }} received</p>
                        <p class="text-xs text-stone-500 dark:text-stone-400">of {{ $peso($row->commission) }} agent commission</p>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-5 py-10 text-center text-stone-500 dark:text-stone-400">No agents assigned yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Month by month --}}
@if($ranking->isNotEmpty())
<div class="overflow-hidden rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900" x-data="{ metric: 'sales', rows: @js($monthlyRows) }">
    <div class="flex flex-col gap-3 border-b border-stone-100 dark:border-stone-800 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
        <h3 class="font-semibold text-stone-800 dark:text-stone-100">Monthly Activity</h3>
        <div class="inline-flex rounded-lg border border-stone-200 dark:border-stone-700 p-0.5 text-xs font-medium" role="tablist">
            @foreach(['sales' => 'Sales', 'inquiries' => 'Inquiries', 'viewings' => 'Viewings'] as $metric => $text)
            <button type="button" role="tab" @click="metric = '{{ $metric }}'" :aria-selected="metric === '{{ $metric }}'"
                    :class="metric === '{{ $metric }}' ? 'bg-teal-700 text-white' : 'text-stone-600 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800'"
                    class="rounded-md px-3 py-1.5 transition">{{ $text }}</button>
            @endforeach
        </div>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-stone-100 dark:border-stone-800 bg-stone-50 dark:bg-stone-800/60">
                <tr class="text-stone-500 dark:text-stone-400">
                    <th class="sticky left-0 bg-stone-50 dark:bg-stone-800 px-5 py-3 text-left font-medium">Agent</th>
                    @foreach($months as $month)
                    <th class="px-3 py-3 text-center font-medium">{{ $month->format('M') }}</th>
                    @endforeach
                    <th class="px-4 py-3 text-center font-medium">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                <template x-for="row in rows" :key="row.name">
                    <tr>
                        <td class="sticky left-0 whitespace-nowrap bg-white dark:bg-stone-900 px-5 py-3 font-medium text-stone-700 dark:text-stone-200" x-text="row.name"></td>
                        <template x-for="(count, i) in row.counts[metric]" :key="i">
                            <td class="px-3 py-3 text-center" :class="count ? 'font-semibold text-stone-800 dark:text-stone-100' : 'text-stone-300 dark:text-stone-600'" x-text="count || '·'"></td>
                        </template>
                        <td class="px-4 py-3 text-center font-bold text-stone-800 dark:text-stone-100" x-text="row.counts[metric].reduce((a, b) => a + b, 0)"></td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>
</div>
@endif
@endsection
