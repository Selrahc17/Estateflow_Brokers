@extends('layouts.admin')
@section('title', 'Audit Logs')
@section('page-title', 'Audit Logs')
@section('page-subtitle', 'Every change made in the system, and who made it')

@section('content')
@php
    $actionStyles = [
        'Created or submitted' => 'bg-teal-100 dark:bg-teal-900 text-teal-800 dark:text-teal-200',
        'Updated' => 'bg-amber-100 dark:bg-amber-950 text-amber-800 dark:text-amber-300',
        'Deleted' => 'bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300',
        'Signed in' => 'bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300',
        'Signed out' => 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300',
    ];
    $fieldClass = 'rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400';
@endphp

<form method="GET" class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-[1fr_140px_180px_150px_150px_auto]">
    <input type="search" name="search" value="{{ request('search') }}" placeholder="Search person or activity..." aria-label="Search logs" class="{{ $fieldClass }}">
    <select name="role" aria-label="Filter by role" class="{{ $fieldClass }}">
        <option value="">All roles</option>
        @foreach(['admin', 'broker', 'agent', 'client'] as $role)
        <option value="{{ $role }}" @selected(request('role') === $role)>{{ ucfirst($role) }}</option>
        @endforeach
    </select>
    <select name="action" aria-label="Filter by action" class="{{ $fieldClass }}">
        <option value="">All actions</option>
        @foreach($actions as $action)
        <option value="{{ $action }}" @selected(request('action') === $action)>{{ $action }}</option>
        @endforeach
    </select>
    <input type="date" name="from" value="{{ request('from') }}" aria-label="From date" class="{{ $fieldClass }}">
    <input type="date" name="to" value="{{ request('to') }}" aria-label="To date" class="{{ $fieldClass }}">
    <div class="flex gap-2">
        <button type="submit" class="flex-1 rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-800">Filter</button>
        @if(request()->hasAny(['search', 'role', 'action', 'from', 'to']))
        <a href="{{ route('admin.audit') }}" class="rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-4 py-2 text-center text-sm font-medium text-stone-600 dark:text-stone-300 transition hover:bg-stone-50 dark:hover:bg-stone-800">Clear</a>
        @endif
    </div>
</form>

@if($errors->any())
<div class="mb-4 rounded-lg border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950 p-3 text-sm text-red-700 dark:text-red-300">{{ $errors->first() }}</div>
@endif

<div class="overflow-hidden rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900">
    <div class="flex items-center justify-between border-b border-stone-100 dark:border-stone-800 px-5 py-4">
        <h2 class="font-semibold text-stone-800 dark:text-stone-100">Activity</h2>
        <span class="text-xs text-stone-500 dark:text-stone-400">{{ $logs->total() }} {{ Str::plural('entry', $logs->total()) }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-stone-100 dark:border-stone-800 bg-stone-50 dark:bg-stone-800/60">
                <tr class="text-left text-stone-500 dark:text-stone-400">
                    <th class="px-5 py-3 font-medium">Time</th>
                    <th class="px-5 py-3 font-medium">Person</th>
                    <th class="px-5 py-3 font-medium">Action</th>
                    <th class="px-5 py-3 font-medium">Details</th>
                    <th class="px-5 py-3 font-medium">Result</th>
                    <th class="px-5 py-3 font-medium">IP Address</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                @forelse($logs as $log)
                <tr class="transition hover:bg-stone-50 dark:hover:bg-stone-800">
                    <td class="whitespace-nowrap px-5 py-3 font-mono text-xs text-stone-500 dark:text-stone-400">{{ $log->created_at->format('M d, Y g:i A') }}</td>
                    <td class="px-5 py-3">
                        <p class="text-xs font-medium text-stone-700 dark:text-stone-200">{{ $log->actor?->name ?? 'Removed user' }}</p>
                        <p class="text-xs text-stone-500 dark:text-stone-400">{{ ucfirst((string) $log->actor_role) }}</p>
                    </td>
                    <td class="px-5 py-3"><span class="whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-medium {{ $actionStyles[$log->action] ?? 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300' }}">{{ $log->action }}</span></td>
                    <td class="max-w-sm truncate px-5 py-3 text-xs text-stone-600 dark:text-stone-300" title="{{ $log->description }}">{{ ucfirst((string) $log->description) }}</td>
                    <td class="px-5 py-3 text-xs {{ (int) $log->status_code >= 400 ? 'text-red-700 dark:text-red-300' : 'text-stone-500 dark:text-stone-400' }}">{{ (int) $log->status_code >= 400 ? 'Failed' : 'OK' }}</td>
                    <td class="px-5 py-3 font-mono text-xs text-stone-500 dark:text-stone-400">{{ $log->ip_address ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-5 py-10 text-center text-stone-500 dark:text-stone-400">No activity found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="border-t border-stone-100 dark:border-stone-800 px-5 py-4">{{ $logs->links() }}</div>
    @endif
</div>
@endsection
