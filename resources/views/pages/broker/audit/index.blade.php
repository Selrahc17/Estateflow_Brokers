@extends('layouts.broker')
@section('title', 'Audit Logs')
@section('page-title', 'Audit Logs')
@section('page-subtitle', 'Track activity from you and your Agents')

@section('content')
@php
    $actionStyles = [
        'Created or submitted' => 'bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200',
        'Updated' => 'bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300',
        'Deleted' => 'bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300',
    ];
@endphp

<form method="GET" class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_1fr_160px_160px_auto]">
    <select name="actor" aria-label="Filter by person" class="rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
        <option value="">Everyone</option>
        <option value="{{ $broker->id }}" @selected((int) request('actor') === $broker->id)>{{ $broker->name }} (you)</option>
        @foreach($agents as $agent)
            <option value="{{ $agent->id }}" @selected((int) request('actor') === $agent->id)>{{ $agent->name }}</option>
        @endforeach
    </select>
    <select name="action" aria-label="Filter by action" class="rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
        <option value="">All actions</option>
        @foreach(array_keys($actionStyles) as $action)
            <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
        @endforeach
    </select>
    <input type="date" name="from" value="{{ request('from') }}" aria-label="From date" class="rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
    <input type="date" name="to" value="{{ request('to') }}" aria-label="To date" class="rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
    <div class="flex gap-2">
        <button type="submit" class="flex-1 rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-800">Filter</button>
        @if(request()->hasAny(['actor', 'action', 'from', 'to']))
            <a href="{{ route('broker.audit.index') }}" class="rounded-lg border border-stone-200 dark:border-stone-700 px-4 py-2 text-center text-sm font-medium text-stone-600 dark:text-stone-300 transition hover:bg-stone-50 dark:hover:bg-stone-800">Clear</a>
        @endif
    </div>
</form>

@if($errors->any())
<div class="mb-5 rounded-lg border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950 px-4 py-3 text-sm text-red-700 dark:text-red-300">{{ $errors->first() }}</div>
@endif

<div class="overflow-hidden rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900">
    <div class="flex items-center justify-between border-b border-stone-100 dark:border-stone-800 px-5 py-4">
        <div>
            <h2 class="font-semibold text-stone-800 dark:text-stone-100">Activity</h2>
            <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">Only your own activity and your Agents' activity is shown.</p>
        </div>
        <span class="text-xs text-teal-700 dark:text-teal-300">{{ $logs->total() }} entr{{ $logs->total() === 1 ? 'y' : 'ies' }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-stone-100 dark:border-stone-800 bg-stone-50 dark:bg-stone-800/60">
                <tr class="text-left text-stone-500 dark:text-stone-400">
                    <th class="px-5 py-3 font-medium">Timestamp</th>
                    <th class="px-5 py-3 font-medium">Person</th>
                    <th class="px-5 py-3 font-medium">Action</th>
                    <th class="px-5 py-3 font-medium">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                @forelse($logs as $log)
                <tr class="transition hover:bg-stone-50 dark:hover:bg-stone-800">
                    <td class="px-5 py-3 font-mono text-xs text-stone-500 dark:text-stone-400">{{ $log->created_at->format('M d, Y g:i A') }}</td>
                    <td class="px-5 py-3 text-xs font-medium text-stone-700 dark:text-stone-200">{{ $log->actor?->name ?? 'Removed user' }}</td>
                    <td class="px-5 py-3"><span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $actionStyles[$log->action] ?? 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300' }}">{{ $log->action }}</span></td>
                    <td class="max-w-md truncate px-5 py-3 text-xs text-stone-500 dark:text-stone-400">{{ ucfirst($log->description) }} ({{ $log->method }})</td>
                </tr>
                @empty
                <tr><td colspan="4" class="px-5 py-10 text-center text-stone-500 dark:text-stone-400">No activity found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
        <div class="border-t border-stone-100 dark:border-stone-800 px-5 py-4">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
