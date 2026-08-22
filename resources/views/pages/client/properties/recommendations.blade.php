@extends('layouts.public')
@section('title', 'Property Match')

@section('content')
<div class="bg-gradient-to-r from-stone-900 to-amber-900 text-white">
    <div class="max-w-6xl mx-auto px-6 py-10">
        <p class="text-amber-400 text-xs uppercase tracking-widest font-semibold mb-1">EstateFlow AI</p>
        <h1 class="text-2xl font-bold">Find Your Property Match</h1>
    </div>
</div>

<div class="max-w-6xl mx-auto px-6 py-8 space-y-6">
    <form method="GET" action="{{ route('client.recommendations') }}" class="bg-white rounded-2xl border border-stone-200 p-6 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
        <div>
            <label class="text-xs text-stone-500 mb-1 block">Property Type</label>
            <input name="type" value="{{ $preferences['type'] ?? '' }}" class="w-full border border-stone-200 rounded-xl px-3 py-2.5 text-sm" placeholder="Condominium">
        </div>
        <div>
            <label class="text-xs text-stone-500 mb-1 block">City</label>
            <input name="city" value="{{ $preferences['city'] ?? '' }}" class="w-full border border-stone-200 rounded-xl px-3 py-2.5 text-sm" placeholder="Quezon City">
        </div>
        <div>
            <label class="text-xs text-stone-500 mb-1 block">Province</label>
            <input name="province" value="{{ $preferences['province'] ?? '' }}" class="w-full border border-stone-200 rounded-xl px-3 py-2.5 text-sm" placeholder="Metro Manila">
        </div>
        <div>
            <label class="text-xs text-stone-500 mb-1 block">Maximum Price</label>
            <input type="number" name="max_price" value="{{ $preferences['max_price'] ?? '' }}" min="0" class="w-full border border-stone-200 rounded-xl px-3 py-2.5 text-sm" placeholder="5000000">
        </div>
        <div>
            <label class="text-xs text-stone-500 mb-1 block">Minimum Bedrooms</label>
            <input type="number" name="bedrooms" value="{{ $preferences['bedrooms'] ?? '' }}" min="0" class="w-full border border-stone-200 rounded-xl px-3 py-2.5 text-sm" placeholder="2">
        </div>
        <button type="submit" class="sm:col-span-2 lg:col-span-5 bg-amber-600 hover:bg-amber-700 text-white px-5 py-3 rounded-xl text-sm font-semibold">Find Matches</button>
    </form>

    @if($properties->isNotEmpty())
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        @foreach($properties as $property)
        <a href="{{ route('client.property.show', $property->slug) }}" class="bg-white rounded-2xl border border-stone-200 overflow-hidden hover:shadow-md transition">
            @if($property->featured_image)
            <img src="{{ $property->featured_image }}" alt="{{ $property->name }}" class="w-full h-44 object-cover">
            @else
            <div class="w-full h-44 bg-stone-100"></div>
            @endif
            <div class="p-5">
                <p class="font-semibold text-stone-800">{{ $property->name }}</p>
                <p class="text-xs text-stone-400 mt-1">{{ $property->type }} · {{ $property->city }}</p>
                <p class="text-lg font-bold text-amber-600 mt-3">₱{{ number_format($property->price, 0) }}</p>
                <p class="text-xs text-stone-400 mt-1">{{ $property->available_lots_count }} available lot(s)</p>
            </div>
        </a>
        @endforeach
    </div>
    @elseif(count($preferences) > 0)
    <div class="bg-white rounded-2xl border border-stone-200 py-14 text-center text-stone-400">No available properties matched those preferences.</div>
    @else
    <div class="bg-white rounded-2xl border border-stone-200 py-14 text-center text-stone-400">Enter your preferences to see property matches.</div>
    @endif
</div>
@endsection
