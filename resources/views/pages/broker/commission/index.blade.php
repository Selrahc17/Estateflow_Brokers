@extends('layouts.broker')

@section('title', 'Commission Management')
@section('page-title', 'Commission Management')
@section('page-subtitle', 'Track broker and agent commission agreements and payout status')

@section('content')
<div class="mb-6 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
    <div>
        <h2 class="text-xl font-semibold text-stone-800">Commission Records</h2>
        <p class="text-sm text-stone-500">Manage the agent payout structure for your team.</p>
    </div>
    <a href="{{ route('broker.commissions.create') }}" class="inline-flex items-center justify-center rounded-xl bg-[var(--color-primary)] px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-[var(--color-primary-dark)]">
        + New Commission
    </a>
</div>

<div class="grid grid-cols-1 gap-4 md:grid-cols-4">
    <div class="rounded-2xl border border-stone-200 bg-gradient-to-br from-white to-teal-50 p-4 shadow-sm">
        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-stone-400">Active Agreements</p>
        <p class="mt-3 text-3xl font-bold text-stone-800">{{ $agreements->where('status', 'active')->count() }}</p>
    </div>
    <div class="rounded-2xl border border-stone-200 bg-gradient-to-br from-amber-50 to-white p-4 shadow-sm">
        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-stone-400">Total Expected</p>
        <p class="mt-3 text-3xl font-bold text-amber-600">₱{{ number_format($totalExpected ?? 0, 2) }}</p>
    </div>
    <div class="rounded-2xl border border-stone-200 bg-gradient-to-br from-emerald-50 to-white p-4 shadow-sm">
        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-stone-400">Paid</p>
        <p class="mt-3 text-3xl font-bold text-emerald-600">₱{{ number_format($totalPaid ?? 0, 2) }}</p>
    </div>
    <div class="rounded-2xl border border-stone-200 bg-gradient-to-br from-rose-50 to-white p-4 shadow-sm">
        <p class="text-[11px] font-semibold uppercase tracking-[0.14em] text-stone-400">Disputed</p>
        <p class="mt-3 text-3xl font-bold text-rose-600">₱{{ number_format($totalDisputed ?? 0, 2) }}</p>
    </div>
</div>

<div class="mt-6 overflow-hidden rounded-2xl border border-stone-200 bg-white shadow-sm">
    <div class="overflow-x-auto">
        <table class="min-w-[980px] w-full divide-y divide-stone-200">
            <thead class="bg-stone-50">
                <tr>
                    <th class="w-[30%] whitespace-nowrap px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-stone-500">Property</th>
                    <th class="w-[20%] whitespace-nowrap px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-stone-500">Agent</th>
                    <th class="w-[12%] whitespace-nowrap px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-stone-500">Rate</th>
                    <th class="w-[15%] whitespace-nowrap px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-stone-500">Schedule</th>
                    <th class="w-[13%] whitespace-nowrap px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-stone-500">Status</th>
                    <th class="w-[150px] whitespace-nowrap px-4 py-3 text-left text-[11px] font-semibold uppercase tracking-[0.14em] text-stone-500">Action</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-200 bg-white">
                @forelse($agreements as $agreement)
                    <tr class="transition hover:bg-stone-50/80">
                        <td class="px-4 py-3">
                            @php
                                $property = $agreement->property;
                                $propertyImage = $property?->featured_image ?: (is_array($property?->images) ? ($property->images[0] ?? null) : null);
                                $propertyImageUrl = $propertyImage && str_starts_with($propertyImage, 'http') ? $propertyImage : ($propertyImage ? asset('storage/' . $propertyImage) : null);
                            @endphp
                            <div class="flex items-center gap-3">
                                <div class="h-11 w-11 shrink-0 overflow-hidden rounded-lg bg-gradient-to-br from-teal-500 to-emerald-500 shadow-sm">
                                    @if($propertyImageUrl)
                                        <img src="{{ $propertyImageUrl }}" alt="{{ $property?->name ?? 'Property' }}" class="h-full w-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-xs font-bold text-white">
                                            {{ Str::upper(Str::substr($property?->name ?? 'P', 0, 1)) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <div class="truncate font-medium text-stone-800">{{ $property?->name ?? 'N/A' }}</div>
                                    <div class="text-[11px] text-stone-500">{{ $agreement->property?->city ?? 'Unknown city' }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <div class="max-w-[180px] truncate font-medium text-stone-800" title="{{ $agreement->agent?->name ?? 'N/A' }}">{{ $agreement->agent?->name ?? 'N/A' }}</div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="inline-flex rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                {{ number_format($agreement->commission_rate, 2) }}%
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="inline-flex rounded-full bg-stone-100 px-2.5 py-1 text-xs font-medium text-stone-600">
                                {{ str_replace('_', ' ', $agreement->payment_schedule) }}
                            </span>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium {{ $agreement->status === 'active' ? 'bg-emerald-100 text-emerald-700' : 'bg-stone-100 text-stone-600' }}">
                                {{ ucfirst($agreement->status) }}
                            </span>
                        </td>
                        <td class="min-w-[150px] whitespace-nowrap px-4 py-3 text-sm">
                            <div class="flex items-center gap-2">
                            <a href="{{ route('broker.commissions.show', $agreement) }}" class="inline-flex shrink-0 items-center gap-1 rounded-lg border border-teal-200 bg-teal-50 px-3 py-1.5 font-medium text-teal-700 transition hover:border-teal-300 hover:bg-teal-100">
                                View
                            </a>
                            <a href="{{ route('broker.commissions.pay', $agreement) }}" class="inline-flex shrink-0 items-center gap-1 rounded-lg bg-[var(--color-primary)] px-3 py-1.5 font-medium text-white transition hover:bg-[var(--color-primary-dark)]">
                                Pay
                            </a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-10 text-center text-sm text-stone-500">No commission agreements created yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

@if($agreements->hasPages())
    <div class="mt-6">{{ $agreements->links() }}</div>
@endif
@endsection
