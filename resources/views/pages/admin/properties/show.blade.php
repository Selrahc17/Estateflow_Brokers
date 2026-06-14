@extends('layouts.admin')
@section('title', $property->name)
@section('page-title', $property->name)
@section('page-subtitle', 'Property Details')

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
    <div class="xl:col-span-2">
        <div class="bg-white rounded-xl border border-stone-200 p-5 mb-5">
            <p class="text-stone-600">{{ $property->description }}</p>
            <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-stone-400">Address</p>
                    <p class="font-medium">{{ $property->address }}</p>
                </div>
                <div>
                    <p class="text-stone-400">Type</p>
                    <p class="font-medium">{{ $property->type }}</p>
                </div>
                <div>
                    <p class="text-stone-400">Price</p>
                    <p class="font-medium text-red-600">₱{{ number_format($property->price, 2) }}</p>
                </div>
                <div>
                    <p class="text-stone-400">Status</p>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $property->status==='available' ? 'bg-green-100 text-green-700' : '' }}
                        {{ $property->status==='hidden' ? 'bg-stone-100 text-stone-700' : '' }}
                        {{ $property->status==='flagged' ? 'bg-red-100 text-red-700' : '' }}">
                        {{ ucfirst($property->status) }}
                    </span>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h3 class="font-semibold text-stone-800 mb-4">Lots</h3>
            @if($property->lots->count() > 0)
            <table class="w-full text-sm">
                <thead class="bg-stone-50 border-b border-stone-100">
                    <tr class="text-left text-stone-500">
                        <th class="px-4 py-3 font-medium">Lot #</th>
                        <th class="px-4 py-3 font-medium">Price</th>
                        <th class="px-4 py-3 font-medium">Size</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100">
                    @foreach($property->lots as $lot)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $lot->lot_number }}</td>
                        <td class="px-4 py-3">₱{{ number_format($lot->price, 2) }}</td>
                        <td class="px-4 py-3">{{ $lot->square_meters }} sqm</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $lot->status==='available' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $lot->status==='reserved' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ $lot->status==='sold' ? 'bg-stone-100 text-stone-700' : '' }}">
                                {{ ucfirst($lot->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <p class="text-stone-400 text-center py-4">No lots available.</p>
            @endif
        </div>
    </div>

    <div>
        <div class="bg-white rounded-xl border border-stone-200 p-5 mb-5">
            <h3 class="font-semibold text-stone-800 mb-4">Manage Status</h3>
            <form method="POST" action="{{ route('admin.properties.status', $property) }}">
                @csrf
                @method('PATCH')
                <label class="block mb-2 text-sm text-stone-600">Update Property Status</label>
                <select name="status" class="w-full border border-stone-200 rounded-lg px-3 py-2 mb-4">
                    <option value="available" {{ $property->status === 'available' ? 'selected' : '' }}>Available</option>
                    <option value="hidden" {{ $property->status === 'hidden' ? 'selected' : '' }}>Hide Property</option>
                    <option value="flagged" {{ $property->status === 'flagged' ? 'selected' : '' }}>Flag as Inappropriate</option>
                </select>
                <button type="submit" class="w-full bg-red-600 text-white py-2 rounded-lg hover:bg-red-700">Update Status</button>
            </form>
        </div>

        <div class="bg-white rounded-xl border border-stone-200 p-5">
            <h3 class="font-semibold text-stone-800 mb-4">Listing Broker</h3>
            @if($property->broker)
            <p class="font-medium">{{ $property->broker->name }}</p>
            <p class="text-xs text-stone-500">{{ $property->broker->email }}</p>
            @else
            <p class="text-stone-400">No broker assigned.</p>
            @endif
        </div>
    </div>
</div>
@endsection
