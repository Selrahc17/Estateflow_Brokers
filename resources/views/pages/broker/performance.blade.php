@extends('layouts.broker')
@section('title', 'Agent Performance')
@section('page-title', 'Agent Performance')
@section('page-subtitle', 'Review the performance of Agents assigned to your account')

@section('content')
<div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700">
    <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100 dark:border-stone-800">
        <h2 class="font-semibold text-stone-800 dark:text-stone-100">Agent Performance</h2>
        <span class="text-xs text-teal-700 dark:text-teal-300">{{ $agents->count() }} Agent{{ $agents->count() !== 1 ? 's' : '' }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 dark:bg-stone-800/60 border-b border-stone-100 dark:border-stone-800">
                <tr class="text-left text-stone-500 dark:text-stone-400">
                    <th class="px-5 py-3 font-medium">Agent Name</th>
                    <th class="px-5 py-3 font-medium">Leads</th>
                    <th class="px-5 py-3 font-medium">Viewings</th>
                    <th class="px-5 py-3 font-medium">Sales</th>
                    <th class="px-5 py-3 font-medium">Commission</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                @forelse($agents as $agent)
                <tr class="hover:bg-stone-50 dark:hover:bg-stone-800 transition">
                    <td class="px-5 py-4">
                        <p class="font-medium text-stone-700 dark:text-stone-200">{{ $agent->name }}</p>
                        <p class="text-xs text-stone-500 dark:text-stone-400">{{ $agent->email }}</p>
                    </td>
                    <td class="px-5 py-4 font-semibold text-blue-600 dark:text-blue-300">{{ $agent->leads_count }}</td>
                    <td class="px-5 py-4 font-semibold text-teal-700 dark:text-teal-300">{{ $agent->viewings_count }}</td>
                    <td class="px-5 py-4 font-semibold text-green-600 dark:text-green-300">{{ $agent->sales_count }}</td>
                    <td class="px-5 py-4">
                        <p class="font-semibold text-stone-700 dark:text-stone-200">₱{{ number_format($agent->commission_paid, 2) }} received</p>
                        <p class="text-xs text-stone-500 dark:text-stone-400">of ₱{{ number_format($agent->commission_expected, 2) }} agent commission</p>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-5 py-10 text-center text-stone-500 dark:text-stone-400">No Agents assigned yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
