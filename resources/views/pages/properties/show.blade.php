@extends('layouts.app')
@section('title', $property->name)
@section('page-title', $property->name)
@section('page-subtitle', $property->city ?? $property->province ?? 'Property Details')

@section('content')
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    {{-- Property Details --}}
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <div class="flex items-start justify-between mb-4">
                <h2 class="text-lg font-semibold text-stone-800">Property Information</h2>
                <div class="flex gap-2">
                    <a href="{{ route('agent.properties.edit', $property) }}" class="text-sm text-teal-700 hover:underline">Edit</a>
                    <form method="POST" action="{{ route('agent.properties.destroy', $property) }}" onsubmit="return confirm('Delete this property?')">
                        @csrf @method('DELETE')
                        <button class="text-sm text-red-500 hover:underline">Delete</button>
                    </form>
                </div>
            </div>

            @if($property->featured_image)
                <img src="{{ $property->featured_image }}" class="w-full h-48 object-cover rounded-lg mb-4">
            @endif

            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div><dt class="text-stone-400">Status</dt><dd class="font-medium">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                        @if($property->status == 'available') bg-green-100 text-green-700
                        @elseif($property->status == 'sold') bg-red-100 text-red-700
                        @else bg-blue-100 text-blue-700 @endif">
                        {{ ucfirst(str_replace('_', ' ', $property->status)) }}
                    </span>
                </dd></div>
                <div><dt class="text-stone-400">Price</dt><dd class="font-medium">₱{{ number_format($property->price, 2) }}</dd></div>
                <div><dt class="text-stone-400">Location</dt><dd class="font-medium">{{ $property->city }}, {{ $property->province }}</dd></div>
                <div><dt class="text-stone-400">Lots</dt><dd class="font-medium">{{ $property->lots->count() }} total</dd></div>
            </dl>
            @if($property->description)
                <div class="mt-4 pt-4 border-t border-stone-100">
                    <p class="text-sm text-stone-600">{{ $property->description }}</p>
                </div>
            @endif

            @if($property->latitude && $property->longitude)
            <div class="mt-4 pt-4 border-t border-stone-100">
                <p class="text-sm font-medium text-stone-700 mb-2 flex items-center gap-1">
                    <svg class="w-4 h-4 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Map Location
                </p>
                <div id="show-map" class="w-full rounded-lg border border-stone-200 overflow-hidden" style="height:220px"></div>
                <p class="text-xs text-stone-400 mt-1 font-mono">{{ $property->latitude }}, {{ $property->longitude }}</p>
            </div>
            @endif
        </div>

        {{-- Lots Table --}}
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-stone-800">Lots</h2>
                <a href="{{ route('agent.lots.create') }}?property_id={{ $property->id }}" class="text-sm bg-teal-700 hover:bg-teal-800 text-white px-3 py-1.5 rounded-lg font-medium transition">Add Lot</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead><tr class="text-left text-stone-400 border-b border-stone-200">
                        <th class="pb-2 font-medium">#</th>
                        <th class="pb-2 font-medium">Title</th>
                        <th class="pb-2 font-medium">SQM</th>
                        <th class="pb-2 font-medium">Price</th>
                        <th class="pb-2 font-medium">Status</th>
                        <th class="pb-2 font-medium"></th>
                    </tr></thead>
                    <tbody>
                    @forelse($property->lots as $lot)
                        <tr class="border-b border-stone-100 hover:bg-stone-50">
                            <td class="py-2.5 text-stone-500">{{ $lot->lot_number }}</td>
                            <td class="py-2.5 font-medium">{{ $lot->title ?? 'N/A' }}</td>
                            <td class="py-2.5">{{ $lot->square_meters ? number_format($lot->square_meters, 2) : '-' }}</td>
                            <td class="py-2.5">₱{{ number_format($lot->price, 2) }}</td>
                            <td class="py-2.5">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    @if($lot->status == 'available') bg-green-100 text-green-700
                                    @elseif($lot->status == 'reserved') bg-yellow-100 text-yellow-700
                                    @else bg-red-100 text-red-700 @endif">
                                    {{ ucfirst($lot->status) }}
                                </span>
                            </td>
                            <td class="py-2.5"><a href="{{ route('agent.lots.edit', $lot) }}" class="text-teal-700 hover:underline text-xs">Edit</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="py-8 text-center text-stone-400">No lots added yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Sidebar --}}
    <div class="space-y-4">
        <div class="bg-white rounded-xl border border-stone-200 p-4">
            <h3 class="text-sm font-semibold text-stone-700 mb-3">Quick Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('agent.lots.create') }}?property_id={{ $property->id }}" class="block text-center text-sm bg-teal-50 hover:bg-teal-100 text-teal-800 py-2 rounded-lg transition font-medium">+ Add Lot</a>
                <a href="{{ route('agent.properties.edit', $property) }}" class="block text-center text-sm bg-stone-50 hover:bg-stone-100 text-stone-600 py-2 rounded-lg transition font-medium">Edit Property</a>
                <a href="{{ route('agent.reservations.create') }}" class="block text-center text-sm bg-stone-50 hover:bg-stone-100 text-stone-600 py-2 rounded-lg transition font-medium">+ New Reservation</a>
            </div>
        </div>
    </div>
</div>

@if($property->latitude && $property->longitude)
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var map = L.map('show-map', { zoomControl: true, scrollWheelZoom: false })
        .setView([{{ $property->latitude }}, {{ $property->longitude }}], 15);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { attribution: '&copy; OpenStreetMap' }).addTo(map);
    L.marker([{{ $property->latitude }}, {{ $property->longitude }}])
        .addTo(map)
        .bindPopup('<strong>{{ addslashes($property->name) }}</strong><br>{{ addslashes(implode(', ', array_filter([$property->city, $property->province]))) }}')
        .openPopup();
});
</script>
@endif
@endsection