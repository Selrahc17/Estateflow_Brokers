@extends('layouts.broker')
@section('title', 'Agent Performance')
@section('page-title', 'Agent Performance')
@section('page-subtitle', 'Review the performance of Agents assigned to your account')

@section('content')
<div class="bg-white rounded-xl border border-stone-200">
    <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
        <h2 class="font-semibold text-stone-800">Agent Performance</h2>
        <span class="text-xs text-red-600">{{ $agents->count() }} Agent{{ $agents->count() !== 1 ? 's' : '' }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-stone-100">
                <tr class="text-left text-stone-500">
                    <th class="px-5 py-3 font-medium">Agent Name</th>
                    <th class="px-5 py-3 font-medium">Leads</th>
                    <th class="px-5 py-3 font-medium">Viewings</th>
                    <th class="px-5 py-3 font-medium">Sales</th>
                    <th class="px-5 py-3 font-medium">Commission</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse($agents as $agent)
                <tr class="hover:bg-stone-50 transition">
                    <td class="px-5 py-4">
                        <p class="font-medium text-stone-700">{{ $agent->name }}</p>
                        <p class="text-xs text-stone-400">{{ $agent->email }}</p>
                    </td>
                    <td class="px-5 py-4 font-semibold text-blue-600">{{ $agent->leads_count }}</td>
                    <td class="px-5 py-4 font-semibold text-teal-700">{{ $agent->viewings_count }}</td>
                    <td class="px-5 py-4 font-semibold text-green-600">{{ $agent->sales_count }}</td>
                    <td class="px-5 py-4 text-stone-400">Not tracked</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-stone-400">No Agents assigned yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
