@extends('layouts.admin')
@section('title', 'Property Reviews')
@section('page-title', 'Property Reviews')
@section('page-subtitle', 'Reviews clients leave on property pages')

@section('content')
@php
    $fieldClass = 'rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400';
    $tabAccent = ['all' => 'text-stone-700 dark:text-stone-200', 'visible' => 'text-green-700 dark:text-green-300', 'hidden' => 'text-stone-500 dark:text-stone-400'];
    $keep = array_filter(['q' => $search, 'rating' => $rating]);
@endphp

@if(session('success'))
<div class="mb-5 rounded-lg border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-950 px-4 py-3 text-sm text-green-700 dark:text-green-300" role="status">{{ session('success') }}</div>
@endif

<nav aria-label="Review visibility" class="mb-5 grid grid-cols-3 gap-3 sm:max-w-xl">
    @foreach($tabs as $value => $label)
    @php $active = $status === $value; @endphp
    <a href="{{ route('admin.reviews', $keep + ['status' => $value]) }}" @if($active) aria-current="page" @endif
       class="rounded-xl border px-4 py-3 transition {{ $active ? 'border-teal-600 bg-white ring-2 ring-teal-600/20 dark:bg-stone-900' : 'border-stone-200 bg-white hover:border-stone-300 dark:border-stone-700 dark:bg-stone-900 dark:hover:border-stone-600' }}">
        <span class="block text-xs font-medium text-stone-500 dark:text-stone-400">{{ $label }}</span>
        <span class="mt-1 block text-2xl font-bold {{ $tabAccent[$value] }}">{{ $counts[$value] }}</span>
    </a>
    @endforeach
</nav>

<form method="GET" class="mb-5 grid grid-cols-1 gap-3 sm:grid-cols-[1fr_180px_auto]">
    <input type="hidden" name="status" value="{{ $status }}">
    <input type="search" name="q" value="{{ $search }}" placeholder="Search client, property, or comment..." aria-label="Search reviews" class="{{ $fieldClass }}">
    <select name="rating" aria-label="Filter by rating" class="{{ $fieldClass }}">
        <option value="">All ratings</option>
        @for($star = 5; $star >= 1; $star--)
        <option value="{{ $star }}" @selected($rating === $star)>{{ $star }} {{ Str::plural('star', $star) }}</option>
        @endfor
    </select>
    <div class="flex gap-2">
        <button type="submit" class="flex-1 rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white transition hover:bg-teal-800">Filter</button>
        @if($search !== '' || $rating)
        <a href="{{ route('admin.reviews', ['status' => $status]) }}" class="rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-4 py-2 text-center text-sm font-medium text-stone-600 dark:text-stone-300 transition hover:bg-stone-50 dark:hover:bg-stone-800">Clear</a>
        @endif
    </div>
</form>

<div class="space-y-3">
    @forelse($reviews as $review)
    <article class="rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 p-5 {{ $review->isHidden() ? 'opacity-75' : '' }}">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
            <div class="min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <p class="font-semibold text-stone-800 dark:text-stone-100">{{ $review->user?->name ?? 'Deleted user' }}</p>
                    <span class="inline-flex items-center gap-1 text-sm font-semibold text-amber-600 dark:text-amber-400">
                        @include('pages.client.properties._stars', ['rating' => $review->rating, 'size' => 'h-3.5 w-3.5'])
                        {{ $review->rating }}/5
                    </span>
                    @if($review->isHidden())
                    <span class="rounded-full bg-stone-200 px-2 py-0.5 text-xs font-medium text-stone-600 dark:bg-stone-800 dark:text-stone-400">Hidden</span>
                    @endif
                </div>
                <p class="mt-0.5 text-xs text-stone-500 dark:text-stone-400">
                    on
                    @if($review->property)
                    <a href="{{ route('client.property.show', $review->property->slug) }}#reviews" target="_blank" rel="noopener" class="font-medium text-teal-700 hover:underline dark:text-teal-400">{{ $review->property->name }}</a>
                    @else
                    a removed property
                    @endif
                    · {{ $review->created_at->format('M d, Y') }}
                    @if($review->updated_at->gt($review->created_at->addMinute())) · edited @endif
                </p>
            </div>
            <form action="{{ route($review->isHidden() ? 'admin.reviews.restore' : 'admin.reviews.hide', $review) }}" method="POST"
                  @unless($review->isHidden()) onsubmit="return confirm('Hide this review from the property page?')" @endunless>
                @csrf
                @method('PATCH')
                <button type="submit" class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition {{ $review->isHidden() ? 'border-teal-200 text-teal-700 hover:bg-teal-50 dark:border-teal-800 dark:text-teal-300 dark:hover:bg-teal-950' : 'border-red-200 text-red-700 hover:bg-red-50 dark:border-red-900 dark:text-red-300 dark:hover:bg-red-950' }}">
                    {{ $review->isHidden() ? 'Show again' : 'Hide' }}
                </button>
            </form>
        </div>
        @if($review->comment)
        <p class="mt-3 whitespace-pre-line break-words text-sm text-stone-700 dark:text-stone-200">{{ $review->comment }}</p>
        @else
        <p class="mt-3 text-sm italic text-stone-500 dark:text-stone-400">Rating only, no comment.</p>
        @endif
    </article>
    @empty
    <div class="rounded-xl border border-dashed border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 px-6 py-14 text-center">
        <p class="font-semibold text-stone-800 dark:text-stone-100">{{ $search !== '' || $rating ? 'No reviews match your search' : 'No reviews yet' }}</p>
        <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">Reviews clients post on property pages will appear here.</p>
    </div>
    @endforelse
</div>

@if($reviews->hasPages())
<div class="mt-5">{{ $reviews->links() }}</div>
@endif
@endsection
