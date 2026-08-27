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
    ] as $stat)
    <div class="bg-white rounded-xl border border-stone-200 p-4 text-center">
        <p class="text-xl font-bold {{ $stat[3] }}">{{ $stat[1] }}</p>
        <p class="text-xs text-stone-500 mt-1">{{ $stat[0] }}</p>
    </div>
    @endforeach
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
