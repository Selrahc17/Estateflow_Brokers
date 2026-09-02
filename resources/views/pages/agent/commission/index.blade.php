@extends('layouts.app')

@section('title', 'My Commission')
@section('page-title', 'My Commission')
@section('page-subtitle', 'View your payout structure and payment schedule')

@section('content')
<div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-3">
    <div class="rounded-xl border border-stone-200 bg-white p-4">
        <p class="text-xs uppercase tracking-wide text-stone-400">Assigned Agreements</p>
        <p class="mt-2 text-2xl font-bold text-stone-800">{{ $agreements->total() }}</p>
    </div>
    <div class="rounded-xl border border-stone-200 bg-white p-4">
        <p class="text-xs uppercase tracking-wide text-stone-400">Pending Payouts</p>
        <p class="mt-2 text-2xl font-bold text-amber-600">{{ $agreements->where('status', 'active')->count() }}</p>
    </div>
    <div class="rounded-xl border border-stone-200 bg-white p-4">
        <p class="text-xs uppercase tracking-wide text-stone-400">My Share</p>
        <p class="mt-2 text-2xl font-bold text-emerald-600">{{ number_format($agreements->sum('agent_share'), 2) }}%</p>
    </div>
</div>

<div class="overflow-hidden rounded-xl border border-stone-200 bg-white">
    <table class="min-w-full divide-y divide-stone-200">
        <thead class="bg-stone-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Property</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Broker</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">My Share</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Schedule</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-200">
            @forelse($agreements as $agreement)
                <tr>
                    <td class="px-4 py-3 text-sm text-stone-700">{{ $agreement->property?->name ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-sm text-stone-700">{{ $agreement->broker?->name ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-sm text-stone-700">{{ number_format($agreement->agent_share, 2) }}%</td>
                    <td class="px-4 py-3 text-sm text-stone-700">{{ str_replace('_', ' ', $agreement->payment_schedule) }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $agreement->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-100 text-stone-600' }}">
                            {{ ucfirst($agreement->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <a href="{{ route('agent.commission.show', $agreement) }}" class="font-medium text-teal-700 hover:text-teal-900">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-sm text-stone-500">No commission agreements assigned to you yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($agreements->hasPages())
    <div class="mt-6">{{ $agreements->links() }}</div>
@endif
@endsection
