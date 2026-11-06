@extends('layouts.broker')
@section('title', 'Team Clients')
@section('page-title', 'Team Clients')
@section('page-subtitle', 'Clients handled by your Agents')

@section('content')
@include('pages.broker.team._filters', [
    'statuses' => ['pending' => 'Pending', 'under_review' => 'Under review', 'approved' => 'Approved', 'rejected' => 'Rejected'],
    'searchPlaceholder' => 'Search name or email...',
])

<div class="overflow-hidden rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-stone-100 dark:border-stone-800 bg-stone-50 dark:bg-stone-800/60">
                <tr class="text-left text-stone-500 dark:text-stone-400">
                    <th class="px-5 py-3 font-medium">Client</th>
                    <th class="px-5 py-3 font-medium">Agent</th>
                    <th class="px-5 py-3 font-medium">Requirements</th>
                    <th class="px-5 py-3 font-medium">Reservations</th>
                    <th class="px-5 py-3 font-medium">Added</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                @forelse($clients as $client)
                <tr class="transition hover:bg-stone-50 dark:hover:bg-stone-800">
                    <td class="px-5 py-3">
                        <a href="{{ route('broker.team.clients.show', $client) }}" class="font-medium text-teal-700 dark:text-teal-300 hover:underline">{{ $client->full_name }}</a>
                        <p class="text-xs text-stone-500 dark:text-stone-400">{{ $client->email }}</p>
                    </td>
                    <td class="px-5 py-3 text-stone-600 dark:text-stone-300">{{ $client->broker?->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-stone-600 dark:text-stone-300">{{ ucfirst(str_replace('_', ' ', $client->qualification_status ?? 'pending')) }}</td>
                    <td class="px-5 py-3 text-stone-600 dark:text-stone-300">{{ $client->reservations_count }}</td>
                    <td class="px-5 py-3 text-xs text-stone-500 dark:text-stone-400">{{ $client->created_at->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-10 text-center text-stone-500 dark:text-stone-400">No clients found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($clients->hasPages())
        <div class="border-t border-stone-100 dark:border-stone-800 px-5 py-4">{{ $clients->links() }}</div>
    @endif
</div>
@endsection
