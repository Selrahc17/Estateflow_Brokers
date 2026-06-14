@extends('layouts.admin')
@section('title', 'Property Management')
@section('page-title', 'Manage Properties')
@section('page-subtitle', 'Review and moderate property listings')

@section('content')
<div class="bg-white rounded-xl border border-stone-200">
    <div class="flex items-center justify-between px-5 py-4 border-b border-stone-100">
        <h2 class="font-semibold text-stone-800">All Properties</h2>
        <form method="GET" class="flex gap-2">
            <input type="text" name="search" placeholder="Search properties..." 
                   value="{{ request('search') }}"
                   class="border border-stone-200 rounded-lg px-3 py-2 text-sm">
            <select name="status" class="border border-stone-200 rounded-lg px-3 py-2 text-sm">
                <option value="">All Statuses</option>
                <option value="available" {{ request('status') === 'available' ? 'selected' : '' }}>Available</option>
                <option value="hidden" {{ request('status') === 'hidden' ? 'selected' : '' }}>Hidden</option>
                <option value="flagged" {{ request('status') === 'flagged' ? 'selected' : '' }}>Flagged</option>
            </select>
            <button type="submit" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm hover:bg-red-700">Search</button>
        </form>
    </div>
    <table class="w-full text-sm">
        <thead class="bg-stone-50 border-b border-stone-100">
            <tr class="text-left text-stone-500">
                <th class="px-5 py-3 font-medium">Property</th>
                <th class="px-5 py-3 font-medium">Broker</th>
                <th class="px-5 py-3 font-medium">Status</th>
                <th class="px-5 py-3 font-medium">Date</th>
                <th class="px-5 py-3 font-medium"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-stone-100">
            @forelse($properties as $property)
            <tr class="hover:bg-stone-50 transition">
                <td class="px-5 py-3">
                    <p class="font-medium text-stone-700">{{ $property->name }}</p>
                    <p class="text-xs text-stone-400">{{ $property->city }}, {{ $property->province }}</p>
                </td>
                <td class="px-5 py-3 text-stone-500">{{ $property->broker?->name ?? '—' }}</td>
                <td class="px-5 py-3">
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $property->status==='available' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $property->status==='hidden' ? 'bg-stone-100 text-stone-700' : '' }}
                        {{ $property->status==='flagged' ? 'bg-red-100 text-red-700' : '' }}">
                        {{ ucfirst($property->status) }}
                    </span>
                </td>
                <td class="px-5 py-3 text-stone-400 text-xs">{{ $property->created_at->format('M d, Y') }}</td>
                <td class="px-5 py-3 flex gap-2">
                    <a href="{{ route('admin.properties.show', $property) }}" class="text-xs text-blue-600 hover:underline">View</a>
                    <form method="POST" action="{{ route('admin.properties.status', $property) }}">
                        @csrf
                        @method('PATCH')
                        <select name="status" onchange="this.form.submit()" class="text-xs border border-stone-200 rounded px-2 py-1">
                            <option value="available" {{ $property->status === 'available' ? 'selected' : '' }}>Available</option>
                            <option value="hidden" {{ $property->status === 'hidden' ? 'selected' : '' }}>Hide</option>
                            <option value="flagged" {{ $property->status === 'flagged' ? 'selected' : '' }}>Flag</option>
                        </select>
                    </form>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="px-5 py-8 text-center text-stone-400">No properties found.</td></tr>
            @endforelse
        </tbody>
    </table>
    <div class="p-5">
        {{ $properties->links() }}
    </div>
</div>
@endsection
