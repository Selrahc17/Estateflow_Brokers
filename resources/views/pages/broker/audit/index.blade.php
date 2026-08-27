@extends('layouts.broker')
@section('title', 'Audit Logs')
@section('page-title', 'Audit Logs')
@section('page-subtitle', 'Track activity from Agents under your account')

@section('content')
<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    <div class="flex items-center justify-between border-b border-stone-100 px-5 py-4">
        <div>
            <h2 class="font-semibold text-stone-800">Agent Activity</h2>
            <p class="mt-1 text-xs text-stone-500">Only activity belonging to your Agents is shown.</p>
        </div>
        <span class="text-xs text-red-600">{{ $logs->count() }} entr{{ $logs->count() === 1 ? 'y' : 'ies' }}</span>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 border-b border-stone-100">
                <tr class="text-left text-stone-500">
                    <th class="px-5 py-3 font-medium">Timestamp</th>
                    <th class="px-5 py-3 font-medium">Agent</th>
                    <th class="px-5 py-3 font-medium">Action</th>
                    <th class="px-5 py-3 font-medium">Details</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @forelse($logs as $log)
                <tr class="hover:bg-stone-50 transition">
                    <td class="px-5 py-3 text-xs font-mono text-stone-400">{{ $log->created_at->format('M d, Y g:i A') }}</td>
                    <td class="px-5 py-3 text-xs font-medium text-stone-700">{{ $log->actor?->name ?? 'Agent' }}</td>
                    <td class="px-5 py-3"><span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-medium text-red-700">{{ $log->action }}</span></td>
                    <td class="max-w-md truncate px-5 py-3 text-xs text-stone-500">{{ ucfirst($log->description) }} ({{ $log->method }})</td>
                    <td class="px-5 py-3"><span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $log->status_code < 400 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' }}">{{ $log->status_code }}</span></td>
                </tr>
                @empty
                <tr><td colspan="5" class="px-5 py-10 text-center text-stone-400">No Agent activity recorded yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
