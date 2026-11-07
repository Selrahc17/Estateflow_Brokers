@extends('layouts.admin')
@section('title', 'Inquiries')
@section('page-title', 'Inquiries')
@section('page-subtitle', 'Monitor leads across all Brokers')

@section('content')
@php
    $fieldClass = 'rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400';
@endphp

<form method="GET" class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_200px_200px_auto]">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search name, email, phone, or property..." aria-label="Search inquiries" class="{{ $fieldClass }}">
    <select name="broker" aria-label="Filter by Broker" class="{{ $fieldClass }}">
        <option value="">All Brokers</option>
        @foreach($brokers as $broker)
        <option value="{{ $broker->id }}" @selected((int) request('broker') === $broker->id)>{{ $broker->name }}</option>
        @endforeach
    </select>
    <select name="status" aria-label="Filter by status" class="{{ $fieldClass }}">
        <option value="">All statuses</option>
        @foreach($statuses as $value => $label)
        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
        @endforeach
    </select>
    <div class="flex gap-2">
        <button type="submit" class="flex-1 rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-800">Filter</button>
        @if(request()->hasAny(['search', 'broker', 'status']))
        <a href="{{ route('admin.inquiries') }}" class="rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-4 py-2 text-center text-sm font-medium text-stone-600 dark:text-stone-300 transition hover:bg-stone-50 dark:hover:bg-stone-800">Clear</a>
        @endif
    </div>
</form>

<div class="overflow-hidden rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900">
    <div class="flex items-center justify-between border-b border-stone-100 dark:border-stone-800 px-5 py-4">
        <h2 class="font-semibold text-stone-800 dark:text-stone-100">All Inquiries</h2>
        <span class="text-xs text-stone-500 dark:text-stone-400">{{ $inquiries->total() }} {{ Str::plural('inquiry', $inquiries->total()) }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-stone-100 dark:border-stone-800 bg-stone-50 dark:bg-stone-800/60">
                <tr class="text-left text-stone-500 dark:text-stone-400">
                    <th class="px-5 py-3 font-medium">Buyer</th>
                    <th class="px-5 py-3 font-medium">Property</th>
                    <th class="px-5 py-3 font-medium">Agent</th>
                    <th class="px-5 py-3 font-medium">Broker</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Received</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                @forelse($inquiries as $inquiry)
                <tr class="align-top transition hover:bg-stone-50 dark:hover:bg-stone-800">
                    <td class="px-5 py-3">
                        <p class="font-medium text-stone-700 dark:text-stone-200">{{ $inquiry->contact_label }}</p>
                        <p class="text-xs text-stone-500 dark:text-stone-400">{{ $inquiry->email ?? '—' }}</p>
                        <p class="text-xs text-stone-500 dark:text-stone-400">{{ $inquiry->phone }}</p>
                        @if($inquiry->facebook)
                        <p class="text-xs text-stone-500 dark:text-stone-400">Facebook: {{ $inquiry->facebook }}</p>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-stone-600 dark:text-stone-300">{{ $inquiry->property?->name ?? 'Property removed' }}</td>
                    <td class="px-5 py-3 text-stone-600 dark:text-stone-300">{{ $inquiry->broker?->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-stone-600 dark:text-stone-300">{{ $inquiry->broker?->broker?->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-stone-600 dark:text-stone-300">{{ $statuses[$inquiry->status] ?? ucfirst(str_replace('_', ' ', $inquiry->status)) }}</td>
                    <td class="whitespace-nowrap px-5 py-3 text-xs text-stone-500 dark:text-stone-400">{{ $inquiry->created_at->format('M d, Y') }}</td>
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
