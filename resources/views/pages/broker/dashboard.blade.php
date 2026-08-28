@extends('layouts.broker')
@section('title', 'Dashboard')
@section('page-title', 'Broker Dashboard')
@section('page-subtitle', 'Team-wide overview and Agent performance')

@section('content')
<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5 mb-6">
    @foreach([
        ['Total Agents', $stats['total_agents'], 'bg-blue-50', 'text-blue-600'],
        ['Active Agents', $stats['active_agents'], 'bg-green-50', 'text-green-600'],
        ['Pending Approval', $stats['pending_agents'], 'bg-teal-50', 'text-teal-700'],
        ['Sales This Month', $stats['sales_this_month'], 'bg-red-50', 'text-red-600'],
        ['Leads This Month', $stats['leads_this_month'], 'bg-indigo-50', 'text-indigo-600'],
    ] as $stat)
    <div class="bg-white rounded-xl border border-stone-200 p-4 text-center">
        <p class="text-xl font-bold {{ $stat[3] }}">{{ $stat[1] }}</p>
        <p class="text-xs text-stone-500 mt-1">{{ $stat[0] }}</p>
    </div>
    @endforeach
</div>

@php
    $chartLabels   = $chart['labels']->toJson();
    $chartSales    = $chart['sales']->toJson();
    $chartLeads    = $chart['leads']->toJson();
    $chartViewings = $chart['viewings']->toJson();
@endphp
<div class="bg-white rounded-xl border border-stone-200 mb-6">
    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-stone-100">
        <div>
            <h2 class="font-semibold text-stone-800">Team Activity</h2>
            <p class="mt-1 text-xs text-stone-500">Monthly activity from your assigned Agents</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <div class="flex items-center gap-3 text-xs text-stone-500 mr-3">
                <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-red-500"></span>Sales</span>
                <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-blue-500"></span>Leads</span>
                <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-teal-600"></span>Viewings</span>
            </div>
            <div class="flex items-center gap-1">
                <button onclick="setChartType('bar')" id="btn-bar" class="chart-type-btn px-3 py-1 rounded-lg text-xs font-medium bg-red-600 text-white transition">Bar</button>
                <button onclick="setChartType('line')" id="btn-line" class="chart-type-btn px-3 py-1 rounded-lg text-xs font-medium bg-stone-100 text-stone-600 hover:bg-stone-200 transition">Line</button>
            </div>
        </div>
    </div>
    <div class="flex flex-wrap gap-1.5 px-5 pt-4">
        <button onclick="showMonth(null)" id="btn-all" class="month-btn px-3 py-1 rounded-full text-xs font-medium bg-red-600 text-white transition">All</button>
        @foreach($chart['labels'] as $i => $label)
        <button onclick="showMonth({{ $i }})" id="btn-month-{{ $i }}" class="month-btn px-3 py-1 rounded-full text-xs font-medium bg-stone-100 text-stone-600 hover:bg-stone-200 transition">{{ $label }}</button>
        @endforeach
    </div>
    <div class="px-5 py-4">
        <canvas id="teamActivityChart" height="100"></canvas>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
const allLabels   = {!! $chartLabels !!};
const allSales    = {!! $chartSales !!};
const allLeads    = {!! $chartLeads !!};
const allViewings = {!! $chartViewings !!};

let currentType = 'bar';
let currentMonth = null;

const ctx = document.getElementById('teamActivityChart').getContext('2d');
const chart = new Chart(ctx, {
    type: 'bar',
    data: buildData(null),
    options: {
        responsive: true,
        plugins: {
            legend: { display: false },
            tooltip: { mode: 'index', intersect: false },
        },
        scales: {
            x: { grid: { display: false }, ticks: { font: { size: 11 } } },
            y: { beginAtZero: true, ticks: { precision: 0, font: { size: 11 } }, grid: { color: '#f5f5f4' } },
        },
    },
});

function buildData(monthIndex) {
    const labels   = monthIndex !== null ? [allLabels[monthIndex]]   : allLabels;
    const sales    = monthIndex !== null ? [allSales[monthIndex]]    : allSales;
    const leads    = monthIndex !== null ? [allLeads[monthIndex]]    : allLeads;
    const viewings = monthIndex !== null ? [allViewings[monthIndex]] : allViewings;
    return {
        labels,
        datasets: [
            { label: 'Sales',    data: sales,    backgroundColor: 'rgba(239,68,68,0.8)',   borderColor: 'rgb(239,68,68)',   borderWidth: 2, tension: 0.4, fill: false, pointRadius: 4 },
            { label: 'Leads',   data: leads,    backgroundColor: 'rgba(59,130,246,0.8)',  borderColor: 'rgb(59,130,246)',  borderWidth: 2, tension: 0.4, fill: false, pointRadius: 4 },
            { label: 'Viewings',data: viewings, backgroundColor: 'rgba(245,158,11,0.8)', borderColor: 'rgb(245,158,11)', borderWidth: 2, tension: 0.4, fill: false, pointRadius: 4 },
        ],
    };
}

function setChartType(type) {
    currentType = type;
    chart.config.type = type;
    chart.update();
    document.querySelectorAll('.chart-type-btn').forEach(b => {
        b.classList.toggle('bg-red-600', b.id === 'btn-' + type);
        b.classList.toggle('text-white', b.id === 'btn-' + type);
        b.classList.toggle('bg-stone-100', b.id !== 'btn-' + type);
        b.classList.toggle('text-stone-600', b.id !== 'btn-' + type);
    });
}

function showMonth(index) {
    currentMonth = index;
    chart.data = buildData(index);
    chart.update();
    document.querySelectorAll('.month-btn').forEach(b => {
        const active = (index === null && b.id === 'btn-all') || b.id === 'btn-month-' + index;
        b.classList.toggle('bg-red-600', active);
        b.classList.toggle('text-white', active);
        b.classList.toggle('bg-stone-100', !active);
        b.classList.toggle('text-stone-600', !active);
    });
}
</script>
@endpush

<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
    <div class="xl:col-span-2 bg-white rounded-xl border border-stone-200">
        <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
            <h2 class="font-semibold text-stone-800">Your Agents</h2>
            <span class="text-xs text-red-600">{{ $stats['total_agents'] }} total</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 border-b border-stone-100">
                    <tr class="text-left text-stone-500">
                        <th class="px-5 py-3 font-medium">Agent</th>
                        <th class="px-5 py-3 font-medium">Email</th>
                        <th class="px-5 py-3 font-medium">Properties</th>
                        <th class="px-5 py-3 font-medium">Clients</th>
                        <th class="px-5 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @forelse($agents as $agent)
                    <tr class="hover:bg-stone-50 transition">
                        <td class="px-5 py-3 font-medium text-stone-700">{{ $agent->name }}</td>
                        <td class="px-5 py-3 text-stone-500 text-xs">{{ $agent->email }}</td>
                        <td class="px-5 py-3 text-stone-500">{{ $agent->properties_count }}</td>
                        <td class="px-5 py-3 text-stone-500">{{ $agent->clients_count }}</td>
                        <td class="px-5 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $agent->is_active ? 'bg-green-100 text-green-700' : 'bg-stone-100 text-stone-600' }}">
                                {{ $agent->is_active ? 'Active' : 'Inactive' }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-5 py-8 text-center text-stone-400">No Agents assigned yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($agents->hasPages())
            <div class="border-t border-stone-100 px-5 py-4">{{ $agents->links() }}</div>
        @endif
    </div>

    <div class="bg-white rounded-xl border border-stone-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 class="font-semibold text-stone-800">Agent Status</h2>
            <span class="text-xs text-red-600">Overview</span>
        </div>
        <div class="space-y-3">
            <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                <span class="text-sm text-stone-600">Active</span>
                <span class="text-sm font-semibold text-green-600">{{ $stats['active_agents'] }}</span>
            </div>
            <div class="flex items-center justify-between border-b border-stone-100 pb-3">
                <span class="text-sm text-stone-600">Pending approval</span>
                <span class="text-sm font-semibold text-teal-700">{{ $stats['pending_agents'] }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-stone-600">Assigned total</span>
                <span class="text-sm font-semibold text-stone-800">{{ $stats['total_agents'] }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
