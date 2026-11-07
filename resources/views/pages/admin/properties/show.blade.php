@extends('layouts.admin')
@section('title', $property->name)
@section('page-title', $property->name)
@section('page-subtitle', 'Property Details')

@section('content')
@if(session('success'))
<div class="mb-5 rounded-lg border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-950 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
@endif
<div class="grid grid-cols-1 xl:grid-cols-3 gap-5">
    <div class="xl:col-span-2">
        <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-5 mb-5">
            <p class="text-stone-600 dark:text-stone-300">{{ $property->description }}</p>
            <div class="mt-4 grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-stone-500 dark:text-stone-400">Address</p>
                    <p class="font-medium">{{ $property->address }}</p>
                </div>
                <div>
                    <p class="text-stone-500 dark:text-stone-400">Type</p>
                    <p class="font-medium">{{ $property->type }}</p>
                </div>
                <div>
                    <p class="text-stone-500 dark:text-stone-400">Price</p>
                    <p class="font-medium text-teal-700 dark:text-teal-300">₱{{ number_format($property->price, 2) }}</p>
                </div>
                <div>
                    <p class="text-stone-500 dark:text-stone-400">Status</p>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium
                        {{ $property->status==='available' ? 'bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300' : '' }}
                        {{ $property->status==='hidden' ? 'bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-200' : '' }}
                        {{ $property->status==='flagged' ? 'bg-red-100 dark:bg-red-950 text-red-700 dark:text-red-300' : '' }}">
                        {{ ucfirst($property->status) }}
                    </span>
                </div>
            </div>
        </div>

        @if($property->type !== 'House and Lot')
        <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-5">
            <h3 class="font-semibold text-stone-800 dark:text-stone-100 mb-4">{{ $property->type === 'Condominium' ? 'Units' : 'Lots' }}</h3>
            @if($property->lots->count() > 0)
            <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-stone-50 dark:bg-stone-800/60 border-b border-stone-100 dark:border-stone-800">
                    <tr class="text-left text-stone-500 dark:text-stone-400">
                        <th class="px-4 py-3 font-medium">Lot #</th>
                        <th class="px-4 py-3 font-medium">Price</th>
                        <th class="px-4 py-3 font-medium">Size</th>
                        <th class="px-4 py-3 font-medium">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                    @foreach($property->lots as $lot)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $lot->lot_number }}</td>
                        <td class="px-4 py-3">₱{{ number_format($lot->price, 2) }}</td>
                        <td class="px-4 py-3">{{ $lot->square_meters }} sqm</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                {{ $lot->status==='available' ? 'bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300' : '' }}
                                {{ $lot->status==='reserved' ? 'bg-yellow-100 dark:bg-yellow-950 text-yellow-700 dark:text-yellow-300' : '' }}
                                {{ $lot->status==='sold' ? 'bg-stone-100 dark:bg-stone-800 text-stone-700 dark:text-stone-200' : '' }}">
                                {{ ucfirst($lot->status) }}
                            </span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
            @else
            <p class="text-stone-500 dark:text-stone-400 text-center py-4">No {{ $property->type === 'Condominium' ? 'units' : 'lots' }} available.</p>
            @endif
        </div>
        @endif
    </div>

    <div>
        <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-5 mb-5">
            <h3 class="font-semibold text-stone-800 dark:text-stone-100 mb-4">Manage Status</h3>
            <form method="POST" action="{{ route('admin.properties.status', $property) }}">
                @csrf
                @method('PATCH')
                <label class="block mb-2 text-sm text-stone-600 dark:text-stone-300">Update Property Status</label>
                <select name="status" class="w-full border border-stone-200 dark:border-stone-700 dark:bg-stone-800 rounded-lg px-3 py-2 mb-4">
                    @foreach(['available' => 'Available', 'coming_soon' => 'Coming Soon', 'sold' => 'Sold', 'hidden' => 'Hide Property', 'flagged' => 'Flag as Inappropriate'] as $value => $text)
                    <option value="{{ $value }}" @selected($property->status === $value)>{{ $text }}</option>
                    @endforeach
                </select>
                <button type="submit" class="w-full bg-teal-700 text-white py-2 rounded-lg text-sm font-semibold transition hover:bg-teal-800">Update Status</button>
            </form>
        </div>

        <div class="bg-white dark:bg-stone-900 rounded-xl border border-stone-200 dark:border-stone-700 p-5">
            <h3 class="font-semibold text-stone-800 dark:text-stone-100 mb-4">Listing Broker</h3>
            @if($property->broker)
            <p class="font-medium">{{ $property->broker->name }}</p>
            <p class="text-xs text-stone-500 dark:text-stone-400">{{ $property->broker->email }}</p>
            @else
            <p class="text-stone-500 dark:text-stone-400">No broker assigned.</p>
            @endif
        </div>
    </div>
</div>
@endsection
