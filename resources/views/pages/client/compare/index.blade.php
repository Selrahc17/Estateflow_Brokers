@extends('layouts.public')
@section('title', 'Compare Properties')

@section('content')
@php
    $summary = session('compare_summary');
    $count = $properties->count();
    $thumb = fn ($property) => $property->gallery_images[0] ?? null;
    $price = fn ($property) => ($value = (float) $property->price ?: $property->lowest_model_price) ? '₱'.number_format($value, 0) : 'Price on request';
@endphp
<div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-stone-800 dark:text-white">Compare properties</h1>
            <p class="mt-1 text-sm text-stone-500 dark:text-stone-400">See up to {{ $max }} listings side by side. The best value in each row is marked.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('client.properties') }}" class="rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-4 py-2 text-sm font-semibold text-stone-700 dark:text-stone-200 transition hover:bg-stone-50 dark:hover:bg-stone-800">Browse properties</a>
            @if($count)
            <form action="{{ route('client.compare.clear') }}" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="rounded-xl px-4 py-2 text-sm font-semibold text-stone-600 dark:text-stone-300 transition hover:bg-stone-100 dark:hover:bg-stone-800">Clear all</button>
            </form>
            @endif
        </div>
    </div>

    @if($count < 2)
    {{-- Not enough to compare yet --}}
    <div class="rounded-2xl border border-dashed border-stone-300 dark:border-stone-700 bg-white dark:bg-stone-900 px-6 py-10 text-center">
        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-teal-50 text-xl text-teal-700 dark:bg-teal-950 dark:text-teal-300" aria-hidden="true">⇄</span>
        <p class="mt-3 font-semibold text-stone-800 dark:text-white">{{ $count === 1 ? 'Add one more property to compare' : 'Pick properties to compare' }}</p>
        <p class="mx-auto mt-1 max-w-md text-sm text-stone-500 dark:text-stone-400">Open any property and tap <span class="font-semibold text-stone-700 dark:text-stone-200">⇄ Compare</span>. You can compare up to {{ $max }} at a time.</p>

        @if($count === 1)
        @php $only = $properties->first(); @endphp
        <div class="mx-auto mt-5 flex max-w-sm items-center gap-3 rounded-xl border border-stone-200 dark:border-stone-700 p-3 text-left">
            <span class="h-14 w-16 shrink-0 overflow-hidden rounded-lg bg-stone-100 dark:bg-stone-800">@if($thumb($only))<img src="{{ $thumb($only) }}" alt="" class="h-full w-full object-cover">@endif</span>
            <div class="min-w-0 flex-1">
                <p class="truncate text-sm font-semibold text-stone-800 dark:text-white">{{ $only->name }}</p>
                <p class="text-xs text-teal-700 dark:text-teal-400">{{ $price($only) }}</p>
            </div>
            <form action="{{ route('client.compare.toggle', $only) }}" method="POST">
                @csrf
                <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Remove</button>
            </form>
        </div>
        @endif
    </div>
    @else

    {{-- AI summary --}}
    <section id="summary" class="mb-6 scroll-mt-24 rounded-2xl border border-teal-100 dark:border-teal-900 bg-gradient-to-br from-teal-50 to-white dark:from-teal-950 dark:to-stone-900 p-5" aria-labelledby="summary-heading">
        <h2 id="summary-heading" class="flex items-center gap-2 font-semibold text-stone-800 dark:text-white">
            <svg class="h-5 w-5 text-teal-700 dark:text-teal-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/></svg>
            Which one fits you?
        </h2>
        <form action="{{ route('client.compare.summary') }}" method="POST" class="mt-3 flex flex-col gap-2 sm:flex-row" x-data="{ busy: false }" @submit="busy = true">
            @csrf
            <label for="compare-needs" class="sr-only">What matters to you?</label>
            <input id="compare-needs" type="text" name="needs" maxlength="300" value="{{ old('needs', $summary['needs'] ?? '') }}"
                   placeholder="Optional: e.g. family of 4, budget ₱3M, near schools"
                   class="min-w-0 flex-1 rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-3 py-2.5 text-sm text-stone-800 dark:text-stone-100 focus:outline-none focus:ring-2 focus:ring-teal-400">
            <button type="submit" :disabled="busy" class="shrink-0 rounded-xl bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800 disabled:opacity-60">
                <span x-show="! busy">Ask AI to compare</span><span x-show="busy" style="display: none">Comparing...</span>
            </button>
        </form>
        @error('needs')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

        @if($summary)
        <div class="mt-4 rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 p-4" role="status">
            <p class="text-xs font-semibold uppercase tracking-wide {{ $summary['ai'] ? 'text-teal-700 dark:text-teal-300' : 'text-stone-500 dark:text-stone-400' }}">{{ $summary['ai'] ? 'AI summary' : 'Quick summary' }}</p>
            <p class="mt-1 whitespace-pre-line text-sm leading-relaxed text-stone-700 dark:text-stone-200">{{ $summary['text'] }}</p>
            <p class="mt-3 text-xs text-stone-500 dark:text-stone-400">
                {{ $summary['ai'] ? 'AI can make mistakes. Confirm prices and details with the agent before you decide.' : 'AI is not available right now, so this summary only uses the numbers in the table.' }}
            </p>
        </div>
        @endif
    </section>

    {{-- Comparison table --}}
    <p class="mb-2 text-xs text-stone-500 dark:text-stone-400 sm:hidden">Swipe sideways to see every property →</p>
    <div class="overflow-x-auto rounded-2xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900">
        <table class="w-full min-w-[640px] table-fixed text-sm">
            <caption class="sr-only">Comparison of {{ $count }} properties</caption>
            <colgroup>
                <col class="w-36 sm:w-44">
                @foreach($properties as $property)<col>@endforeach
            </colgroup>
            <thead>
                <tr class="align-top">
                    <th scope="col" class="sticky left-0 z-10 bg-white dark:bg-stone-900 p-4 text-left text-xs font-semibold uppercase tracking-wide text-stone-500 dark:text-stone-400">{{ $count }} of {{ $max }}</th>
                    @foreach($properties as $property)
                    <th scope="col" class="border-l border-stone-100 dark:border-stone-800 p-4 text-left font-normal">
                        <a href="{{ route('client.property.show', $property->slug) }}" class="group block">
                            <span class="block aspect-[4/3] overflow-hidden rounded-xl bg-stone-100 dark:bg-stone-800">
                                @if($thumb($property))<img src="{{ $thumb($property) }}" alt="" class="h-full w-full object-cover transition group-hover:scale-105" loading="lazy">@endif
                            </span>
                            <span class="mt-2 block font-semibold text-stone-800 group-hover:text-teal-700 dark:text-white dark:group-hover:text-teal-400">{{ $property->name }}</span>
                        </a>
                        <span class="block text-base font-bold text-teal-700 dark:text-teal-400">{{ $price($property) }}</span>
                        <form action="{{ route('client.compare.toggle', $property) }}" method="POST" class="mt-1">
                            @csrf
                            <button type="submit" class="text-xs font-semibold text-stone-500 hover:text-red-600 dark:text-stone-400" aria-label="Remove {{ $property->name }} from comparison">Remove</button>
                        </form>
                    </th>
                    @endforeach
                </tr>
            </thead>
            @foreach($sections as $section => $rows)
            <tbody>
                <tr>
                    <th scope="colgroup" colspan="{{ $count + 1 }}" class="sticky left-0 bg-stone-50 dark:bg-stone-800/60 px-4 py-2 text-left text-xs font-semibold uppercase tracking-wide text-stone-600 dark:text-stone-300">{{ $section }}</th>
                </tr>
                @foreach($rows as $row)
                <tr class="border-t border-stone-100 dark:border-stone-800 align-top">
                    <th scope="row" class="sticky left-0 z-10 bg-white dark:bg-stone-900 px-4 py-3 text-left text-xs font-medium text-stone-500 dark:text-stone-400">{{ $row['label'] }}</th>
                    @foreach($row['values'] as $index => $value)
                    @php $isBest = in_array($index, $row['best'], true); @endphp
                    <td class="border-l border-stone-100 dark:border-stone-800 px-4 py-3 break-words {{ $isBest ? 'bg-green-50/70 dark:bg-green-950/40' : '' }}">
                        @if($value === null)
                        <span class="text-stone-400 dark:text-stone-500">Not listed</span>
                        @else
                        <span class="{{ $isBest ? 'font-semibold text-green-800 dark:text-green-300' : 'text-stone-700 dark:text-stone-200' }}">{{ $value }}</span>
                        @if($isBest)<span class="ml-1 inline-block rounded-full bg-green-600 px-1.5 py-0.5 align-middle text-[10px] font-bold uppercase text-white">Best</span>@endif
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach
            </tbody>
            @endforeach
        </table>
    </div>
    @endif

    {{-- Quick add from saved and viewed listings --}}
    @if($suggestions->isNotEmpty())
    <section class="mt-8" aria-labelledby="suggest-heading">
        <h2 id="suggest-heading" class="font-semibold text-stone-800 dark:text-white">Add from your saved and recently viewed properties</h2>
        <div class="mt-3 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($suggestions as $suggestion)
            <div class="flex items-center gap-3 rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 p-3">
                <span class="h-14 w-16 shrink-0 overflow-hidden rounded-lg bg-stone-100 dark:bg-stone-800">@if($thumb($suggestion))<img src="{{ $thumb($suggestion) }}" alt="" class="h-full w-full object-cover" loading="lazy">@endif</span>
                <div class="min-w-0 flex-1">
                    <a href="{{ route('client.property.show', $suggestion->slug) }}" class="block truncate text-sm font-semibold text-stone-800 hover:text-teal-700 dark:text-white">{{ $suggestion->name }}</a>
                    <p class="text-xs text-teal-700 dark:text-teal-400">{{ $price($suggestion) }}</p>
                </div>
                <form action="{{ route('client.compare.toggle', $suggestion) }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-lg border border-teal-200 dark:border-teal-800 px-3 py-1.5 text-xs font-semibold text-teal-700 transition hover:bg-teal-50 dark:text-teal-300 dark:hover:bg-teal-950">Add</button>
                </form>
            </div>
            @endforeach
        </div>
    </section>
    @endif
</div>
@endsection
