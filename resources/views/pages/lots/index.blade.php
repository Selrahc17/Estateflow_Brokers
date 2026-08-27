@extends('layouts.app')
@section('title', 'Lot Availability')
@section('page-title', 'Lot Inventory')
@section('page-subtitle', 'Manage your lot inventory across properties')

@section('content')
<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <form method="GET" class="flex gap-2">
        <select name="property_id" class="border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" onchange="this.form.submit()">
            <option value="">All Properties</option>
            @foreach($properties as $p)
                <option value="{{ $p->id }}" {{ request('property_id') == $p->id ? 'selected' : '' }}>{{ $p->name }}</option>
            @endforeach
        </select>
        <select name="status" class="border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400" onchange="this.form.submit()">
            <option value="">All Status</option>
            <option value="available" {{ request('status') == 'available' ? 'selected' : '' }}>Available</option>
            <option value="reserved" {{ request('status') == 'reserved' ? 'selected' : '' }}>Reserved</option>
            <option value="sold" {{ request('status') == 'sold' ? 'selected' : '' }}>Sold</option>
        </select>
    </form>
    <a href="{{ route('agent.lots.create') }}" class="inline-flex bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium items-center gap-2 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Lot
    </a>
</div>

<div class="bg-white rounded-xl border border-stone-200 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead><tr class="text-left text-stone-400 bg-stone-50 border-b border-stone-200">
                <th class="px-4 py-3 font-medium">Lot #</th>
                <th class="px-4 py-3 font-medium">Title</th>
                <th class="px-4 py-3 font-medium">Property</th>
                <th class="px-4 py-3 font-medium">SQM</th>
                <th class="px-4 py-3 font-medium">Price</th>
                <th class="px-4 py-3 font-medium">Status</th>
                <th class="px-4 py-3 font-medium"></th>
            </tr></thead>
            <tbody>
            @forelse($lots as $lot)
                <tr class="border-b border-stone-100 hover:bg-stone-50">
                    <td class="px-4 py-3 text-stone-500">{{ $lot->lot_number }}</td>
                    <td class="px-4 py-3 font-medium">{{ $lot->title ?? 'N/A' }}</td>
                    <td class="px-4 py-3">{{ $lot->property->name ?? 'N/A' }}</td>
                    <td class="px-4 py-3">{{ $lot->square_meters ? number_format($lot->square_meters, 2) : '-' }}</td>
                    <td class="px-4 py-3">₱{{ number_format($lot->price, 2) }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            @if($lot->status == 'available') bg-green-100 text-green-700
                            @elseif($lot->status == 'reserved') bg-yellow-100 text-yellow-700
                            @else bg-red-100 text-red-700 @endif">
                            {{ ucfirst($lot->status) }}
                        </span>
                    </td>
                    <td class="px-4 py-3"><a href="{{ route('agent.lots.edit', $lot) }}" class="text-amber-600 hover:underline text-xs">Edit</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-stone-400">No lots found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
<div class="mt-4">{{ $lots->links() }}</div>
@endsection