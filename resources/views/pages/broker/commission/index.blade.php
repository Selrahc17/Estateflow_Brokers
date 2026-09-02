@extends('layouts.broker')

@section('title', 'Commission Management')
@section('page-title', 'Commission Management')
@section('page-subtitle', 'Track broker and agent commission agreements and payout status')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <h2 class="text-xl font-semibold text-stone-800">Commission Records</h2>
        <p class="text-sm text-stone-500">Manage the agent payout structure for your team.</p>
    </div>
    <a href="{{ route('broker.commissions.create') }}" class="inline-flex items-center rounded-lg bg-[var(--color-primary)] px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-[var(--color-primary-dark)] transition">
        + New Commission
    </a>
</div>

<div class="grid grid-cols-1 gap-4 md:grid-cols-4">
    <div class="rounded-xl border border-stone-200 bg-white p-4">
        <p class="text-xs uppercase tracking-wide text-stone-400">Active Agreements</p>
        <p class="mt-2 text-2xl font-bold text-stone-800">{{ $agreements->where('status', 'active')->count() }}</p>
    </div>
    <div class="rounded-xl border border-stone-200 bg-white p-4">
        <p class="text-xs uppercase tracking-wide text-stone-400">Total Expected</p>
        <p class="mt-2 text-2xl font-bold text-amber-600">₱{{ number_format($totalExpected ?? 0, 2) }}</p>
    </div>
    <div class="rounded-xl border border-stone-200 bg-white p-4">
        <p class="text-xs uppercase tracking-wide text-stone-400">Paid</p>
        <p class="mt-2 text-2xl font-bold text-emerald-600">₱{{ number_format($totalPaid ?? 0, 2) }}</p>
    </div>
    <div class="rounded-xl border border-stone-200 bg-white p-4">
        <p class="text-xs uppercase tracking-wide text-stone-400">Disputed</p>
        <p class="mt-2 text-2xl font-bold text-rose-600">₱{{ number_format($totalDisputed ?? 0, 2) }}</p>
    </div>
</div>

<div class="mt-6 overflow-hidden rounded-xl border border-stone-200 bg-white">
    <table class="min-w-full divide-y divide-stone-200">
        <thead class="bg-stone-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Property</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Agent</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Rate</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Schedule</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Status</th>
                <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">Action</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-200">
            @forelse($agreements as $agreement)
                <tr>
                    <td class="px-4 py-3 text-sm text-stone-700">{{ $agreement->property?->name ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-sm text-stone-700">{{ $agreement->agent?->name ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-sm text-stone-700">{{ number_format($agreement->commission_rate, 2) }}%</td>
                    <td class="px-4 py-3 text-sm text-stone-700">{{ str_replace('_', ' ', $agreement->payment_schedule) }}</td>
                    <td class="px-4 py-3">
                        <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $agreement->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-100 text-stone-600' }}">
                            {{ ucfirst($agreement->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <a href="{{ route('broker.commissions.show', $agreement) }}" class="font-medium text-teal-700 hover:text-teal-900">View</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-sm text-stone-500">No commission agreements created yet.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($agreements->hasPages())
    <div class="mt-6">{{ $agreements->links() }}</div>
@endif
@endsection
