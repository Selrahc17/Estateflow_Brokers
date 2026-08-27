@extends('layouts.app')
@section('title', 'My Commission')
@section('page-title', 'My Commission')
@section('page-subtitle', 'Review commission details for your property listings')

@section('content')
<div class="mb-5">
    <h2 class="font-semibold text-stone-800">My Property Listings</h2>
    <p class="mt-1 text-sm text-stone-500">{{ $properties->total() }} listing{{ $properties->total() !== 1 ? 's' : '' }}</p>
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
            <h3 class="font-semibold text-stone-800">{{ $property->name }}</h3>
            <p class="mt-1 text-lg font-bold text-green-600">{{ $property->price ? '₱' . number_format($property->price, 2) : 'Price not set' }}</p>
            <dl class="mt-4 space-y-3 border-t border-stone-100 pt-4">
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-xs text-stone-400">Commission Rate</dt>
                    <dd class="text-sm font-semibold text-amber-600">{{ $property->agent_commission !== null ? number_format($property->agent_commission, 2) . '%' : 'Not set' }}</dd>
                </div>
                <div class="flex items-center justify-between gap-3">
                    <dt class="text-xs text-stone-400">Valid Until</dt>
                    <dd class="text-sm font-medium text-stone-600">{{ $property->valid_until?->format('M d, Y') ?? 'Not set' }}</dd>
                </div>
            </dl>
        </div>
    </article>
    @empty
    <div class="col-span-full rounded-xl border border-dashed border-stone-300 bg-white px-5 py-12 text-center text-stone-500">You have not posted any properties yet.</div>
    @endforelse
</div>

@if($properties->hasPages())
    <div class="mt-6">{{ $properties->links() }}</div>
@endif
@endsection
