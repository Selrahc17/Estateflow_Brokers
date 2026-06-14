@extends('layouts.app')
@section('title', 'My Favorites')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-stone-800">My Favorite Properties</h1>
        <p class="text-stone-400 mt-1">{{ $favorites->total() }} properties saved</p>
    </div>

    @if($favorites->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($favorites as $favorite)
                @include('pages.client.properties._card', ['property' => $favorite->property])
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $favorites->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <div class="text-5xl mb-3">🤍</div>
            <h3 class="text-xl font-semibold text-stone-700 mb-2">No saved properties yet</h3>
            <p class="text-stone-400 mb-6">Start exploring properties and save your favorites!</p>
            <a href="{{ route('client.properties') }}" class="inline-block bg-amber-600 hover:bg-amber-700 text-white px-6 py-3 rounded-xl font-semibold transition">
                Browse Properties
            </a>
        </div>
    @endif
</div>
@endsection
