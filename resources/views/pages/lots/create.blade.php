@extends('layouts.app')
@section('title', 'Add Lot')
@section('page-title', 'Add New Lot')
@section('page-subtitle', 'Create a new lot in your property')

@section('content')
<div class="max-w-2xl mx-auto bg-white rounded-xl border border-stone-200 p-6">
    <form method="POST" action="{{ route('agent.lots.store') }}">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Property</label>
                <select name="property_id" required class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                    <option value="">Select Property</option>
                    @foreach($properties as $id => $name)
                        <option value="{{ $id }}" {{ request('property_id') == $id ? 'selected' : '' }}>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Lot Number</label>
                    <input type="text" name="lot_number" value="{{ old('lot_number') }}" required
                           class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Title (optional)</label>
                    <input type="text" name="title" value="{{ old('title') }}"
                           class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Price (₱)</label>
                    <input type="number" name="price" step="0.01" value="{{ old('price') }}"
                           class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Square Meters</label>
                    <input type="number" name="square_meters" step="0.01" value="{{ old('square_meters') }}"
                           class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Status</label>
                <select name="status" class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                    <option value="available">Available</option>
                    <option value="reserved">Reserved</option>
                    <option value="sold">Sold</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Description</label>
                <textarea name="description" rows="3" class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">{{ old('description') }}</textarea>
            </div>
        </div>
        <div class="flex items-center gap-3 mt-6 pt-6 border-t border-stone-200">
            <button type="submit" class="bg-teal-700 hover:bg-teal-800 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition">Create Lot</button>
            <a href="{{ route('agent.lots.index') }}" class="text-stone-500 hover:text-stone-700 text-sm">Cancel</a>
        </div>
    </form>
</div>
@endsection