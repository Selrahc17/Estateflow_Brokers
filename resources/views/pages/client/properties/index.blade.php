@extends('layouts.public')
@section('title', 'All Properties')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    {{-- Page Header --}}
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-stone-800 dark:text-white">
            @if(request('search'))
                Results for "{{ request('search') }}"
            @elseif(request('location'))
                Properties in "{{ request('location') }}"
            @else
                All Properties
            @endif
        </h1>
        <p class="text-stone-400 text-sm mt-1">{{ $properties->total() }} {{ Str::plural('property', $properties->total()) }} found</p>
    </div>

    @if($properties->isEmpty())
    <div class="py-20 text-center">
        <svg class="w-16 h-16 text-stone-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        <p class="text-stone-400 text-lg font-medium">No properties found</p>
        <p class="text-stone-300 text-sm mt-1">Try adjusting your search or browse all properties</p>
        <a href="{{ route('client.properties') }}" class="mt-4 inline-block bg-amber-600 hover:bg-amber-700 text-white px-5 py-2 rounded-xl text-sm font-medium transition">Browse All</a>
    </div>
    @else
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @foreach($properties as $property)
            @include('pages.client.properties._card', ['property' => $property])
        @endforeach
    </div>

    <div class="mt-8">{{ $properties->withQueryString()->links() }}</div>
    @endif

</div>

@endsection
