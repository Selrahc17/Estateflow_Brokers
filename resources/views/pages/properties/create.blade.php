@extends('layouts.app')
@section('title', 'Add Property')
@section('page-title', 'Add New Property')
@section('page-subtitle', 'Create a new property listing')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl border border-stone-200 p-6">
    <form method="POST" action="{{ route('broker.properties.store') }}" enctype="multipart/form-data">
        @csrf

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Property Name</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 @error('name') border-red-400 @enderror">
                @error('name') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Description</label>
                <textarea name="description" rows="4" class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">{{ old('description') }}</textarea>
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">City</label>
                    <input type="text" name="city" value="{{ old('city') }}"
                           class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Province</label>
                    <input type="text" name="province" value="{{ old('province') }}"
                           class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Address</label>
                <input type="text" name="address" value="{{ old('address') }}"
                       class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>

            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Price (₱)</label>
                    <input type="number" name="price" step="0.01" value="{{ old('price') }}"
                           class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Status</label>
                    <select name="status" class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        <option value="available">Available</option>
                        <option value="coming_soon">Coming Soon</option>
                        <option value="sold">Sold</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Featured Image</label>
                <input type="file" name="featured_image" accept="image/*"
                       class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
            </div>
        </div>

        <div class="flex items-center gap-3 mt-6 pt-6 border-t border-stone-200">
            <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition">Create Property</button>
            <a href="{{ route('broker.properties.index') }}" class="text-stone-500 hover:text-stone-700 text-sm">Cancel</a>
        </div>
    </form>
</div>
@endsection