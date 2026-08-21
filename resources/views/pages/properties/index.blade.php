@extends('layouts.app')
@section('title', 'Properties')
@section('page-title', 'Property Listings')
@section('page-subtitle', 'Manage all your property listings')

@section('content')

{{-- Header Actions --}}
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div>
        <form method="GET" class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <input type="text" name="search" placeholder="Search properties..." value="{{ request('search') }}"
                   class="border border-stone-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 w-full sm:w-64">
            <select name="type" class="border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" onchange="this.form.submit()">
                <option value="">All Types</option>
                @foreach(['House and Lot','Condominium','Townhouse','Lot Only','Office Space','Warehouse','Farm','Villa','Apartment'] as $type)
                <option value="{{ $type }}" {{ request('type') == $type ? 'selected' : '' }}>{{ $type }}</option>
                @endforeach
            </select>
            <select name="status" class="border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" onchange="this.form.submit()">
                <option value="">All Status</option>
                <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
                <option value="sold" {{ request('status') == 'sold' ? 'selected' : '' }}>Sold</option>
                <option value="coming_soon" {{ request('status') == 'coming_soon' ? 'selected' : '' }}>Coming Soon</option>
            </select>
        </form>
    </div>
    <a href="{{ route('broker.properties.create') }}" class="inline-flex bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium items-center gap-2 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Property
    </a>
</div>

{{-- Property Cards --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
    @forelse($properties as $property)
    <div class="bg-white rounded-xl border border-stone-200 overflow-hidden hover:shadow-md transition">
        <div class="h-36 bg-gradient-to-br from-amber-100 to-stone-200 relative overflow-hidden">
            @if($property->featured_image)
                <img src="{{ $property->featured_image }}" class="w-full h-full object-cover absolute inset-0" alt="{{ $property->name }}">
            @else
                <div class="w-full h-full flex items-center justify-center">
                    <svg class="w-16 h-16 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                </div>
            @endif
            <span class="absolute top-2 right-2 px-2 py-1 rounded-full text-xs font-medium
                @if($property->status == 'available') bg-green-100 text-green-700
                @elseif($property->status == 'sold') bg-red-100 text-red-700
                @else bg-blue-100 text-blue-700 @endif">
                {{ ucfirst(str_replace('_', ' ', $property->status)) }}
            </span>
        </div>
        <div class="p-4">
            <div class="flex items-start justify-between mb-2">
                <div>
                    <h3 class="font-semibold text-stone-800">{{ $property->name }}</h3>
                    <p class="text-xs text-stone-400 flex flex-wrap items-center gap-1 mt-0.5">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                        {{ $property->city ?? $property->province ?? 'N/A' }}
                    </p>
                    <p class="text-xs text-stone-500 mt-2 inline-flex items-center gap-1 px-2 py-1 rounded-full bg-stone-100 text-stone-700">{{ $property->type }}</p>
                </div>
            </div>
            <div class="flex items-center justify-between text-xs text-stone-500 mt-3 pt-3 border-t border-stone-100">
                <span>{{ $property->available_lots_count }} available / {{ $property->lots_count }} total units</span>
                @if($property->price)
                    <span class="text-green-600 font-medium">₱{{ number_format($property->price, 2) }}</span>
                @endif
            </div>
            <div class="flex gap-2 mt-3">
                <a href="{{ route('broker.properties.show', $property) }}" class="flex-1 text-center text-xs bg-amber-50 hover:bg-amber-100 text-amber-700 py-1.5 rounded-lg transition font-medium">View Lots</a>
                <a href="{{ route('broker.properties.edit', $property) }}" class="flex-1 text-center text-xs bg-stone-50 hover:bg-stone-100 text-stone-600 py-1.5 rounded-lg transition font-medium">Edit</a>
                <form method="POST" action="{{ route('broker.properties.destroy', $property) }}" onsubmit="return confirm('Delete this property?')" class="w-24">
                    @csrf @method('DELETE')
                    <button type="submit" class="w-full text-center text-xs bg-red-50 hover:bg-red-100 text-red-600 py-1.5 rounded-lg transition font-medium">Delete</button>
                </form>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-full text-center py-12 text-stone-400">
        <svg class="w-12 h-12 mx-auto mb-3 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        <p>No properties found.</p>
        <a href="{{ route('broker.properties.create') }}" class="text-amber-600 hover:underline text-sm mt-1 inline-block">Add your first property</a>
    </div>
    @endforelse
</div>

<div class="mt-6">
    {{ $properties->links() }}
</div>

@endsection