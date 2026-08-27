@extends('layouts.broker')
@section('title', 'Dashboard')
@section('page-title', 'Broker Dashboard')
@section('page-subtitle', 'Team-wide overview and Agent performance')

@section('content')
<div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5 mb-6">
    @foreach([
        ['Total Agents', $stats['total_agents'], 'bg-blue-50', 'text-blue-600'],
        ['Active Agents', $stats['active_agents'], 'bg-green-50', 'text-green-600'],
        ['Pending Approval', $stats['pending_agents'], 'bg-amber-50', 'text-amber-600'],
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
    $chartMax = max(1, max($chart['sales']->all()), max($chart['leads']->all()), max($chart['viewings']->all()));
@endphp
<div class="bg-white rounded-xl border border-stone-200 mb-6">
    <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-4 border-b border-stone-100">
        <div>
            <h2 class="font-semibold text-stone-800">Team Activity</h2>
            <p class="mt-1 text-xs text-stone-500">Monthly activity from your assigned Agents</p>
        </div>
        <div class="flex items-center gap-4 text-xs text-stone-500">
            <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-red-500"></span>Sales</span>
            <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-blue-500"></span>Leads</span>
            <span class="flex items-center gap-1.5"><span class="h-2 w-2 rounded-full bg-amber-500"></span>Viewings</span>
        </div>
    </div>
    <div class="grid grid-cols-6 gap-2 px-5 pt-5 sm:gap-4">
        @foreach($chart['labels'] as $index => $label)
        <div class="flex min-w-0 flex-col items-center gap-2">
            <div class="flex h-40 w-full items-end justify-center gap-0.5 sm:gap-1">
                @foreach([
                    [$chart['sales'][$index], 'bg-red-500'],
                    [$chart['leads'][$index], 'bg-blue-500'],
                    [$chart['viewings'][$index], 'bg-amber-500'],
                ] as [$value, $color])
                <div class="group relative flex h-full w-1/4 items-end">
                    <div class="w-full rounded-t-sm {{ $color }} transition-opacity group-hover:opacity-75" style="height: {{ max(8, ($value / $chartMax) * 100) }}%" title="{{ $value }}"></div>
                    <span class="pointer-events-none absolute -top-5 left-1/2 -translate-x-1/2 text-[10px] text-stone-500 opacity-0 group-hover:opacity-100">{{ $value }}</span>
                </div>
                @endforeach
            </div>
            <span class="text-xs text-stone-400">{{ $label }}</span>
        </div>
        @endforeach
    </div>
    <div class="flex justify-end gap-5 px-5 py-4 text-xs text-stone-500">
        <span>Viewings this month: <strong class="text-stone-700">{{ $stats['viewings_this_month'] }}</strong></span>
    </div>
</div>

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
                <span class="text-sm font-semibold text-amber-600">{{ $stats['pending_agents'] }}</span>
            </div>
            <div class="flex items-center justify-between">
                <span class="text-sm text-stone-600">Assigned total</span>
                <span class="text-sm font-semibold text-stone-800">{{ $stats['total_agents'] }}</span>
            </div>
        </div>
    </div>
</div>
@endsection
