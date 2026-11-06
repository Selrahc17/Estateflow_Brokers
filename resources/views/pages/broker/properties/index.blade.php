@extends('layouts.broker')
@section('title', 'Agent Property Lists')
@section('page-title', 'Agent Property Lists')
@section('page-subtitle', 'Review properties listed by Agents under your account')

@section('content')
@if(session('success'))
<div class="mb-5 rounded-lg border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-950 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
@endif
<div class="mb-5 flex items-center justify-between">
    <div>
        <h2 class="font-semibold text-stone-800 dark:text-stone-100">Property Listings</h2>
        <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">{{ $properties->total() }} listing{{ $properties->total() !== 1 ? 's' : '' }} from your Agents</p>
    </div>
</div>

<div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
    @forelse($properties as $property)
    <article class="flex h-full flex-col overflow-hidden rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 shadow-sm transition hover:shadow-md">
        <div class="relative h-44 shrink-0 overflow-hidden bg-stone-200 sm:h-48">
            @if($property->featured_image)
                <img src="{{ $property->featured_image }}" alt="{{ $property->name }}" class="h-full w-full object-cover object-center">
            @else
                <div class="flex h-full items-center justify-center bg-stone-100 dark:bg-stone-800 text-stone-400">
                    <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16l5-5 4 4 3-3 6 6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            @endif
            <span class="absolute right-3 top-3 rounded-full px-2.5 py-1 text-[11px] font-medium {{ $property->status === 'available' ? 'bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300' : ($property->status === 'sold' ? 'bg-red-100 dark:bg-red-950 text-red-600 dark:text-red-300' : 'bg-stone-100 dark:bg-stone-800 text-stone-600 dark:text-stone-300') }}">
                {{ ucfirst(str_replace('_', ' ', $property->status)) }}
            </span>
        </div>

        <div class="flex flex-1 flex-col p-4 sm:p-5">
            <div class="flex items-start justify-between gap-3">
                <h3 class="min-h-[2.8rem] flex-1 break-words text-base font-semibold leading-6 text-stone-800 dark:text-stone-100">{{ $property->name }}</h3>
                <span class="shrink-0 max-w-[38%] text-right text-sm font-semibold text-green-600 dark:text-green-300">{{ $property->price ? '₱' . number_format($property->price, 2) : 'Price not set' }}</span>
            </div>

            <div class="mt-3 min-h-[2.5rem] space-y-1 text-xs text-stone-500 dark:text-stone-400">
                <p class="break-words"><span class="font-medium text-stone-400">Agent:</span> {{ $property->broker?->name ?? 'Unassigned' }}</p>
                @if(!empty($property->city) || !empty($property->province))
                    <p class="break-words"><span class="font-medium text-stone-400">Location:</span> {{ trim(($property->city ?? '') . ' ' . ($property->province ?? '')) ?: 'Not specified' }}</p>
                @endif
            </div>

            <form action="{{ route('broker.property-lists.terms', $property) }}" method="POST" class="mt-4 space-y-3 border-t border-stone-100 dark:border-stone-800 pt-4">
                @csrf
                @method('PATCH')
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="text-[11px] font-medium uppercase tracking-wide text-stone-400">Commission Rate (%)</label>
                        <input type="number" name="agent_commission" min="0" max="100" step="0.01" value="{{ old('agent_commission', $property->agent_commission) }}" class="mt-1 w-full rounded-lg border border-stone-200 dark:border-stone-700 px-2.5 py-2 text-sm text-stone-700 dark:text-stone-200 focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-200">
                    </div>
                    <div>
                        <label class="text-[11px] font-medium uppercase tracking-wide text-stone-400">Valid Until</label>
                        <input type="date" name="valid_until" value="{{ old('valid_until', $property->valid_until?->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border border-stone-200 dark:border-stone-700 px-2.5 py-2 text-sm text-stone-700 dark:text-stone-200 focus:border-teal-400 focus:outline-none focus:ring-2 focus:ring-teal-200">
                    </div>
                </div>
                <button type="submit" class="w-full rounded-lg bg-teal-700 px-3 py-2.5 text-xs font-medium text-white transition hover:bg-teal-800">Save Terms</button>
            </form>
        </div>
    </article>
    @empty
    <div class="col-span-full rounded-xl border border-dashed border-stone-300 bg-white dark:bg-stone-900 px-5 py-12 text-center text-stone-500 dark:text-stone-400">
        No properties have been listed by your Agents yet.
    </div>
    @endforelse
</div>

@if($properties->hasPages())
    <div class="mt-6">{{ $properties->links() }}</div>
@endif
@endsection
