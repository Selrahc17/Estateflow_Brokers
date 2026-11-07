@extends('layouts.broker')
@section('title', 'Team Inquiries')
@section('page-title', 'Team Inquiries')
@section('page-subtitle', 'Inquiries received by your Agents')

@section('content')
@include('pages.broker.team._filters', [
    'statuses' => [
        'new' => 'New',
        'contacted' => 'Contacted',
        'site_visit_scheduled' => 'Site visit scheduled',
        'negotiating' => 'Negotiating',
        'closed' => 'Closed',
        'lost' => 'Lost',
    ],
    'searchPlaceholder' => 'Search name, email, property, or message...',
])

<div class="overflow-hidden rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-stone-100 dark:border-stone-800 bg-stone-50 dark:bg-stone-800/60">
                <tr class="text-left text-stone-500 dark:text-stone-400">
                    <th class="px-5 py-3 font-medium">Property</th>
                    <th class="px-5 py-3 font-medium">From</th>
                    <th class="px-5 py-3 font-medium">Agent</th>
                    <th class="px-5 py-3 font-medium">Message</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Received</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                @forelse($inquiries as $inquiry)
                <tr class="align-top transition hover:bg-stone-50 dark:hover:bg-stone-800">
                    <td class="px-5 py-3 font-medium text-stone-700 dark:text-stone-200">{{ $inquiry->property?->name ?? 'Property removed' }}</td>
                    <td class="px-5 py-3 text-stone-600 dark:text-stone-300">
                        <p class="font-medium">{{ $inquiry->contact_label }}</p>
                        <p class="text-xs text-stone-500 dark:text-stone-400">{{ $inquiry->email ?? '—' }}</p>
                        <p class="text-xs text-stone-500 dark:text-stone-400">{{ $inquiry->phone }}</p>
                        @if($inquiry->facebook)
                        <p class="text-xs text-stone-500 dark:text-stone-400">Facebook: {{ $inquiry->facebook }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-stone-600 dark:text-stone-300">{{ $inquiry->broker?->name ?? '—' }}</td>
                    <td class="max-w-xs px-5 py-3 text-xs text-stone-500 dark:text-stone-400">{{ $inquiry->message }}</td>
                    <td class="px-5 py-3 text-stone-600 dark:text-stone-300">{{ ucfirst(str_replace('_', ' ', $inquiry->status)) }}</td>
                    <td class="px-5 py-3 text-xs text-stone-500 dark:text-stone-400">{{ $inquiry->created_at->format('M d, Y') }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-stone-500 dark:text-stone-400">No inquiries found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($inquiries->hasPages())
        <div class="border-t border-stone-100 dark:border-stone-800 px-5 py-4">{{ $inquiries->links() }}</div>
    @endif
</div>
@endsection
