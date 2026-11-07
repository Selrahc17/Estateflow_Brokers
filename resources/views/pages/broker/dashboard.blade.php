@extends('layouts.broker')
@section('title', 'Dashboard')
@section('page-title', 'Broker Dashboard')
@section('page-subtitle', 'Team-wide overview and Agent performance')

@section('content')
<div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5">
    @foreach([
        ['Total Agents', $stats['total_agents'], 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
        ['Active Agents', $stats['active_agents'], 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Pending Invites', $stats['pending_invites'], 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z'],
        ['Sales This Month', $stats['sales_this_month'], 'M12 8c-2.21 0-4 1.12-4 2.5s1.79 2.5 4 2.5 4 1.12 4 2.5-1.79 2.5-4 2.5m0-10V5m0 13v1m8-7a8 8 0 11-16 0 8 8 0 0116 0z'],
        ['Leads This Month', $stats['leads_this_month'], 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
    ] as [$label, $value, $icon])
        @include('layouts.partials.stat-tile', compact('label', 'value', 'icon'))
    @endforeach
</div>

@if($renewals->isNotEmpty())
<section class="mb-6 rounded-xl border border-amber-200 dark:border-amber-900 bg-amber-50 dark:bg-amber-950 p-5" aria-labelledby="renewals-heading">
    <h2 id="renewals-heading" class="font-semibold text-stone-800 dark:text-stone-100">Renewals Needing Attention</h2>
    <ul class="mt-3 divide-y divide-amber-200 dark:divide-amber-900">
        @foreach($renewals as $renewal)
        <li class="flex items-center justify-between gap-3 py-2 text-sm">
            <a href="{{ $renewal['link'] }}" class="font-medium text-stone-700 dark:text-stone-200 hover:underline">{{ $renewal['name'] }}</a>
            <span class="shrink-0 font-medium {{ $renewal['state'] === 'expired' ? 'text-red-700 dark:text-red-300' : 'text-amber-800 dark:text-amber-300' }}">{{ $renewal['label'] }}</span>
        </li>
        @endforeach
    </ul>
</section>
@endif

@php
    $chartLabels   = $chart['labels']->toJson();
    $chartSales    = $chart['sales']->toJson();
    $chartLeads    = $chart['leads']->toJson();
    $chartViewings = $chart['viewings']->toJson();
@endphp
<div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 mb-6">
    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-stone-100 dark:border-stone-800">
        <div>
            <h2 class="font-semibold text-stone-800 dark:text-stone-100">Team Activity</h2>
            <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">Monthly activity from your assigned Agents</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <div class="flex items-center gap-3 text-xs text-stone-500 dark:text-stone-400 mr-3">
                <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-red-500"></span>Sales</span>
                <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-blue-500"></span>Leads</span>
                <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-teal-500"></span>Viewings</span>
            </div>
            <div class="flex items-center gap-1">
                <button onclick="setChartType('bar')" id="btn-bar" class="chart-type-btn chart-type-active px-3 py-1 rounded-lg text-xs font-medium bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 transition">Bar</button>
                <button onclick="setChartType('line')" id="btn-line" class="chart-type-btn px-3 py-1 rounded-lg text-xs font-medium bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300 hover:bg-stone-200 dark:hover:bg-stone-700 transition">Line</button>
            </div>
        </div>
    </div>
    <div class="flex items-center gap-3 px-5 pt-4">
        <label for="activity-month" class="text-xs font-semibold text-stone-500 dark:text-stone-400">Through month</label>
        <select id="activity-month" onchange="showMonth(this.value === '' ? null : Number(this.value))"
                class="rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-3 py-2 text-sm font-medium text-stone-700 dark:text-stone-200 focus:border-[var(--color-primary)] focus:outline-none focus:ring-2 focus:ring-[var(--color-accent)]">
            @foreach($chart['labels'] as $i => $label)
            <option value="{{ $i }}" {{ $i === count($chart['labels']) - 1 ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
            <option value="">All months</option>
        </select>
    </div>
    <div class="px-5 py-4">
        <canvas id="teamActivityChart" height="100"></canvas>
    </div>
</div>

@push('scripts')
<style>
    .chart-type-btn.chart-type-active { background-color: var(--color-teal-700); color: #fff; }
</style>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const allLabels   = {!! $chartLabels !!};
const allSales    = {!! $chartSales !!};
const allLeads    = {!! $chartLeads !!};
const allViewings = {!! $chartViewings !!};

let currentType = 'bar';
let currentMonth = allLabels.length - 1;

const chartColors = () => document.documentElement.classList.contains('dark')
    ? { text: '#a6a09b', grid: '#292524' }
    : { text: '#79716b', grid: '#f5f5f4' };

const ctx = document.getElementById('teamActivityChart').getContext('2d');
const chart = new Chart(ctx, {
    type: 'bar',
    data: buildData(currentMonth),
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { mode: 'index', intersect: false },
        },
        scales: {
            x: { grid: { display: false }, ticks: { color: chartColors().text, font: { size: 11 } } },
            y: { beginAtZero: true, ticks: { color: chartColors().text, precision: 0, font: { size: 11 } }, grid: { color: chartColors().grid } },
        },
    },
});

window.addEventListener('theme-changed', () => {
    const { text, grid } = chartColors();
    chart.options.scales.x.ticks.color = text;
    chart.options.scales.y.ticks.color = text;
    chart.options.scales.y.grid.color = grid;
    chart.update();
});

function buildData(monthIndex) {
    const start = monthIndex !== null ? Math.max(0, monthIndex - 4) : 0;
    const end = monthIndex !== null ? monthIndex + 1 : allLabels.length;
    const labels   = allLabels.slice(start, end);
    const sales    = allSales.slice(start, end);
    const leads    = allLeads.slice(start, end);
    const viewings = allViewings.slice(start, end);
    return {
        labels,
        datasets: [
            { label: 'Sales',    data: sales,    backgroundColor: 'rgba(239,68,68,0.8)',   borderColor: 'rgb(239,68,68)',   borderWidth: 2, tension: 0.4, fill: false, pointRadius: 4 },
            { label: 'Leads',   data: leads,    backgroundColor: 'rgba(59,130,246,0.8)',  borderColor: 'rgb(59,130,246)',  borderWidth: 2, tension: 0.4, fill: false, pointRadius: 4 },
            { label: 'Viewings',data: viewings, backgroundColor: 'rgba(59,141,152,0.8)', borderColor: 'rgb(59,141,152)', borderWidth: 2, tension: 0.4, fill: false, pointRadius: 4 },
        ],
    };
}

function setChartType(type) {
    currentType = type;
    chart.config.type = type;
    chart.update();
    document.querySelectorAll('.chart-type-btn').forEach(b => {
        const active = b.id === 'btn-' + type;
        b.classList.toggle('chart-type-active', active);
    });
}

function showMonth(index) {
    currentMonth = index;
    chart.data = buildData(index);
    chart.update();
}
</script>
@endpush

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
    <div class="xl:col-span-2 bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100 dark:border-stone-800">
            <h2 class="font-semibold text-stone-800 dark:text-stone-100">Your Agents</h2>
            <span class="text-xs text-teal-700 dark:text-teal-300">{{ $stats['total_agents'] }} total</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 dark:bg-stone-800/60 border-b border-stone-100 dark:border-stone-800">
                    <tr class="text-left text-stone-500 dark:text-stone-400">
                        <th class="px-5 py-3 font-medium">Agent</th>
                        <th class="px-5 py-3 font-medium">Email</th>
                        <th class="px-5 py-3 font-medium">Properties</th>
                        <th class="px-5 py-3 font-medium">Clients</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                    @forelse($agents as $agent)
                    <tr class="hover:bg-stone-50 dark:hover:bg-stone-800 transition">
                        <td class="px-5 py-3 font-medium text-stone-700 dark:text-stone-200">{{ $agent->name }}</td>
                        <td class="px-5 py-3 text-stone-500 dark:text-stone-400 text-xs">{{ $agent->email }}</td>
                        <td class="px-5 py-3 text-stone-500 dark:text-stone-400">{{ $agent->properties_count }}</td>
                        <td class="px-5 py-3 text-stone-500 dark:text-stone-400">{{ $agent->clients_count }}</td>
                        <td class="px-5 py-3">
                            @if($agent->isInvited())
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300">Invited</span>
                            @else
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $agent->is_active ? 'bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300' : 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300' }}">
                                {{ $agent->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-stone-500 dark:text-stone-400">No Agents assigned yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($agents->hasPages())
            <div class="border-t border-stone-100 dark:border-stone-800 px-5 py-4">{{ $agents->links() }}</div>
        @endif
    </div>

    <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-stone-800 dark:text-stone-100">Agent Status</h2>
            <span class="text-xs text-teal-700 dark:text-teal-300">Overview</span>
        </div>
        <div class="space-y-3">
            <div class="flex items-center justify-between border-b border-stone-100 dark:border-stone-800 pb-3">
                <span class="text-sm text-stone-600 dark:text-stone-300">Active</span>
                <span class="text-sm font-semibold text-green-600 dark:text-green-300">{{ $stats['active_agents'] }}</span>
            </div>
            <div class="flex items-center justify-between border-b border-stone-100 dark:border-stone-800 pb-3">
                <span class="text-sm text-stone-600 dark:text-stone-300">Pending invites</span>
                <span class="text-sm font-semibold text-teal-700 dark:text-teal-300">{{ $stats['pending_invites'] }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-stone-600 dark:text-stone-300">Assigned total</span>
                <span class="text-sm font-semibold text-stone-800 dark:text-stone-100">{{ $stats['total_agents'] }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
