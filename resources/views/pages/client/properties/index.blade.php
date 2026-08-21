@extends('layouts.public')
@section('title', 'All Properties')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-stone-800 dark:text-white">
            @if(request('search'))
                Results for "{{ request('search') }}"
            @elseif(request('city'))
                Properties in {{ request('city') }}
            @elseif(request('province'))
                Properties in {{ request('province') }}
            @elseif(request('location'))
                Properties in "{{ request('location') }}"
            @else
                All Properties
            @endif
        </h1>
        <p class="text-stone-400 text-sm mt-1">{{ $properties->total() }} {{ Str::plural('property', $properties->total()) }} found</p>
    </div>

    <div class="flex flex-col lg:flex-row gap-6" x-data="{ filtersOpen: false }">

        {{-- Mobile Filter Toggle --}}
        <div class="lg:hidden">
            <button @click="filtersOpen = !filtersOpen"
                class="flex items-center gap-2 border border-stone-200 rounded-xl px-4 py-2.5 text-sm font-medium text-stone-600 bg-white w-full justify-between">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2a1 1 0 01-.293.707L13 13.414V19a1 1 0 01-.553.894l-4 2A1 1 0 017 21v-7.586L3.293 6.707A1 1 0 013 6V4z"/></svg>
                    Filters
                    @php $activeFilters = collect(['type','city','province','min_price','max_price','sort'])->filter(fn($k) => request($k))->count(); @endphp
                    @if($activeFilters > 0)
                        <span class="bg-amber-600 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center">{{ $activeFilters }}</span>
                    @endif
                </span>
                <svg class="w-4 h-4 transition" :class="filtersOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
        </div>

        {{-- Filter Sidebar --}}
        <aside class="lg:w-64 shrink-0" x-show="filtersOpen || window.innerWidth >= 1024" x-cloak>
            <form method="GET" action="{{ route('client.properties') }}" id="filter-form">

                {{-- Preserve search --}}
                @if(request('search'))
                    <input type="hidden" name="search" value="{{ request('search') }}">
                @endif

                <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">

                    {{-- Header --}}
                    <div class="px-5 py-4 border-b border-stone-100 flex items-center justify-between">
                        <h3 class="font-semibold text-stone-800 text-sm">Filters</h3>
                        @if($activeFilters > 0)
                            <a href="{{ route('client.properties', request('search') ? ['search' => request('search')] : []) }}"
                               class="text-xs text-amber-600 hover:underline">Clear all</a>
                        @endif
                    </div>

                    {{-- Location: Province --}}
                    @if($provinces->isNotEmpty())
                    <div class="px-5 py-4 border-b border-stone-100">
                        <p class="text-xs font-semibold text-stone-500 uppercase tracking-wider mb-3">Province</p>
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            @foreach($provinces as $province)
                            <label class="flex items-center gap-2.5 cursor-pointer group">
                                <input type="radio" name="province" value="{{ $province }}"
                                    {{ request('province') === $province ? 'checked' : '' }}
                                    onchange="this.form.submit()"
                                    class="w-4 h-4 text-amber-600 border-stone-300 focus:ring-amber-400">
                                <span class="text-sm text-stone-600 group-hover:text-amber-600 transition">{{ $province }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Location: City --}}
                    @if($cities->isNotEmpty())
                    <div class="px-5 py-4 border-b border-stone-100">
                        <p class="text-xs font-semibold text-stone-500 uppercase tracking-wider mb-3">City / Municipality</p>
                        <div class="space-y-2 max-h-48 overflow-y-auto">
                            @foreach($cities as $city)
                            <label class="flex items-center gap-2.5 cursor-pointer group">
                                <input type="radio" name="city" value="{{ $city }}"
                                    {{ request('city') === $city ? 'checked' : '' }}
                                    onchange="this.form.submit()"
                                    class="w-4 h-4 text-amber-600 border-stone-300 focus:ring-amber-400">
                                <span class="text-sm text-stone-600 group-hover:text-amber-600 transition">{{ $city }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Property Type --}}
                    @if($types->isNotEmpty())
                    <div class="px-5 py-4 border-b border-stone-100">
                        <p class="text-xs font-semibold text-stone-500 uppercase tracking-wider mb-3">Property Type</p>
                        <div class="space-y-2">
                            @foreach($types as $type)
                            <label class="flex items-center gap-2.5 cursor-pointer group">
                                <input type="radio" name="type" value="{{ $type }}"
                                    {{ request('type') === $type ? 'checked' : '' }}
                                    onchange="this.form.submit()"
                                    class="w-4 h-4 text-amber-600 border-stone-300 focus:ring-amber-400">
                                <span class="text-sm text-stone-600 group-hover:text-amber-600 transition">{{ $type }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Price Range --}}
                    <div class="px-5 py-4 border-b border-stone-100">
                        <p class="text-xs font-semibold text-stone-500 uppercase tracking-wider mb-3">Price Range</p>
                        <div class="space-y-2">
                            <div>
                                <label class="text-xs text-stone-400 mb-1 block">Min Price (₱)</label>
                                <input type="number" name="min_price" value="{{ request('min_price') }}"
                                    placeholder="0"
                                    class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                            </div>
                            <div>
                                <label class="text-xs text-stone-400 mb-1 block">Max Price (₱)</label>
                                <input type="number" name="max_price" value="{{ request('max_price') }}"
                                    placeholder="Any"
                                    class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                            </div>
                            <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white py-2 rounded-lg text-sm font-medium transition">
                                Apply Price
                            </button>
                        </div>
                    </div>

                    {{-- Sort --}}
                    <div class="px-5 py-4">
                        <p class="text-xs font-semibold text-stone-500 uppercase tracking-wider mb-3">Sort By</p>
                        <select name="sort" onchange="this.form.submit()"
                            class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 bg-white">
                            <option value="newest" {{ request('sort','newest') === 'newest' ? 'selected' : '' }}>Newest First</option>
                            <option value="oldest" {{ request('sort') === 'oldest' ? 'selected' : '' }}>Oldest First</option>
                            <option value="price_asc" {{ request('sort') === 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_desc" {{ request('sort') === 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                        </select>
                    </div>

                </div>
            </form>
        </aside>

        {{-- Results --}}
        <div class="flex-1 min-w-0">

            {{-- Active filter chips --}}
            @if($activeFilters > 0)
            <div class="flex flex-wrap gap-2 mb-4">
                @if(request('province'))
                    <span class="flex items-center gap-1.5 bg-amber-100 text-amber-700 text-xs font-medium px-3 py-1.5 rounded-full">
                        📍 {{ request('province') }}
                        <a href="{{ request()->fullUrlWithoutQuery(['province']) }}" class="hover:text-amber-900">✕</a>
                    </span>
                @endif
                @if(request('city'))
                    <span class="flex items-center gap-1.5 bg-amber-100 text-amber-700 text-xs font-medium px-3 py-1.5 rounded-full">
                        🏙 {{ request('city') }}
                        <a href="{{ request()->fullUrlWithoutQuery(['city']) }}" class="hover:text-amber-900">✕</a>
                    </span>
                @endif
                @if(request('type'))
                    <span class="flex items-center gap-1.5 bg-amber-100 text-amber-700 text-xs font-medium px-3 py-1.5 rounded-full">
                        🏠 {{ request('type') }}
                        <a href="{{ request()->fullUrlWithoutQuery(['type']) }}" class="hover:text-amber-900">✕</a>
                    </span>
                @endif
                @if(request('min_price') || request('max_price'))
                    <span class="flex items-center gap-1.5 bg-amber-100 text-amber-700 text-xs font-medium px-3 py-1.5 rounded-full">
                        💰 ₱{{ request('min_price') ? number_format(request('min_price')) : '0' }} – {{ request('max_price') ? '₱'.number_format(request('max_price')) : 'Any' }}
                        <a href="{{ request()->fullUrlWithoutQuery(['min_price','max_price']) }}" class="hover:text-amber-900">✕</a>
                    </span>
                @endif
            </div>
            @endif

            @if($properties->isEmpty())
            <div class="py-20 text-center bg-white rounded-2xl border border-stone-200">
                <svg class="w-16 h-16 text-stone-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                <p class="text-stone-400 text-lg font-medium">No properties found</p>
                <p class="text-stone-300 text-sm mt-1">Try adjusting your filters</p>
                <a href="{{ route('client.properties') }}" class="mt-4 inline-block bg-amber-600 hover:bg-amber-700 text-white px-5 py-2 rounded-xl text-sm font-medium transition">Clear Filters</a>
            </div>
            @else
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-5">
                @foreach($properties as $property)
                    @include('pages.client.properties._card', ['property' => $property])
                @endforeach
            </div>
            <div class="mt-8">{{ $properties->withQueryString()->links() }}</div>
            @endif

        </div>
    </div>

</div>

@push('styles')
<style>
    [x-cloak] { display: none !important; }
</style>
@endpush

@endsection
