@extends('layouts.broker')
@section('title', 'Agent Property Lists')
@section('page-title', 'Agent Property Lists')
@section('page-subtitle', 'Review properties listed by Agents under your account')

@section('content')
@if(session('success'))
<div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('success') }}</div>
@endif
<div class="mb-5 flex items-center justify-between">
    <div>
        <h2 class="font-semibold text-stone-800">Property Listings</h2>
        <p class="mt-1 text-sm text-stone-500">{{ $properties->total() }} listing{{ $properties->total() !== 1 ? 's' : '' }} from your Agents</p>
    </div>
</div>

<div class="grid grid-cols-1 gap-5 md:grid-cols-2 xl:grid-cols-3">
    @forelse($properties as $property)
    <article class="overflow-hidden rounded-xl border border-stone-200 bg-white">
        <div class="relative h-44 bg-stone-200">
            @if($property->featured_image)
                <img src="{{ $property->featured_image }}" alt="{{ $property->name }}" class="h-full w-full object-cover">
            @else
                <div class="flex h-full items-center justify-center bg-stone-100 text-stone-400">
                    <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 16l5-5 4 4 3-3 6 6M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
            @endif
            <span class="absolute right-3 top-3 rounded-full px-2.5 py-1 text-xs font-medium {{ $property->status === 'available' ? 'bg-green-100 text-green-700' : ($property->status === 'sold' ? 'bg-red-100 text-red-600' : 'bg-stone-100 text-stone-600') }}">
                {{ ucfirst(str_replace('_', ' ', $property->status)) }}
            </span>
        </div>
        <div class="p-5">
            <div class="flex items-start justify-between gap-3">
                <h3 class="font-semibold text-stone-800">{{ $property->name }}</h3>
                <span class="shrink-0 text-sm font-semibold text-green-600">{{ $property->price ? '₱' . number_format($property->price, 2) : 'Price not set' }}</span>
            </div>
            <p class="mt-1 text-xs text-stone-500">Agent: {{ $property->broker?->name ?? 'Unassigned' }}</p>
            <form action="{{ route('broker.property-lists.terms', $property) }}" method="POST" class="mt-4 space-y-3 border-t border-stone-100 pt-4">
                @csrf
                @method('PATCH')
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs text-stone-400">Commission Rate (%)</label>
                        <input type="number" name="agent_commission" min="0" max="100" step="0.01" value="{{ old('agent_commission', $property->agent_commission) }}" class="mt-1 w-full rounded-lg border border-stone-200 px-2.5 py-2 text-sm focus:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-400">
                    </div>
                    <div>
                        <label class="text-xs text-stone-400">Valid Until</label>
                        <input type="date" name="valid_until" value="{{ old('valid_until', $property->valid_until?->format('Y-m-d')) }}" class="mt-1 w-full rounded-lg border border-stone-200 px-2.5 py-2 text-sm focus:border-red-400 focus:outline-none focus:ring-2 focus:ring-red-400">
                    </div>
                </div>
                <button type="submit" class="w-full rounded-lg bg-red-600 py-2 text-xs font-medium text-white transition hover:bg-red-700">Save Terms</button>
            </form>
        </div>
    </article>
    @empty
    <div class="col-span-full rounded-xl border border-dashed border-stone-300 bg-white px-5 py-12 text-center text-stone-500">
        No properties have been listed by your Agents yet.
    </div>
    @endforelse
</div>

@if($properties->hasPages())
    <div class="mt-6">{{ $properties->links() }}</div>
@endif
@endsection
