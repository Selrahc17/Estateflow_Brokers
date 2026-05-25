@extends('layouts.app')
@section('title', 'Edit Property')
@section('page-title', 'Edit Property')
@section('page-subtitle', $property->name)

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl border border-stone-200 p-6">
    <form method="POST" action="{{ route('broker.properties.update', $property) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Property Name</label>
                <input type="text" name="name" value="{{ old('name', $property->name) }}" required
                       class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Description</label>
                <textarea name="description" rows="4" class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">{{ old('description', $property->description) }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">City</label>
                    <input type="text" name="city" value="{{ old('city', $property->city) }}"
                           class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Province</label>
                    <input type="text" name="province" value="{{ old('province', $property->province) }}"
                           class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Price (₱)</label>
                    <input type="number" name="price" step="0.01" value="{{ old('price', $property->price) }}"
                           class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Status</label>
                    <select name="status" class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        <option value="available" {{ $property->status == 'available' ? 'selected' : '' }}>Available</option>
                        <option value="coming_soon" {{ $property->status == 'coming_soon' ? 'selected' : '' }}>Coming Soon</option>
                        <option value="sold" {{ $property->status == 'sold' ? 'selected' : '' }}>Sold</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Featured Image</label>
                @if($property->featured_image)
                    <div class="mb-2"><img src="{{ Storage::url($property->featured_image) }}" class="h-24 rounded-lg"></div>
                @endif
                <input type="file" name="featured_image" accept="image/*"
                       class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
            </div>
        </div>

        <div class="flex items-center gap-3 mt-6 pt-6 border-t border-stone-200">
            <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition">Update Property</button>
            <a href="{{ route('broker.properties.index') }}" class="text-stone-500 hover:text-stone-700 text-sm">Cancel</a>
        </div>
    </form>
</div>
@endsection