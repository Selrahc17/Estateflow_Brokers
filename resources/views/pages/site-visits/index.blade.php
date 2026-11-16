@extends('layouts.app')
@section('title', 'Site Visits')
@section('page-title', 'Site Visits')
@section('page-subtitle', 'Your viewing schedule with buyers')

@section('content')
@php
    $tone = [
        'pending' => 'bg-amber-100 text-amber-800',
        'confirmed' => 'bg-teal-100 text-teal-800',
        'completed' => 'bg-green-100 text-green-800',
        'cancelled' => 'bg-stone-100 text-stone-600',
    ];
    $label = ['pending' => 'Waiting to confirm', 'confirmed' => 'Confirmed', 'completed' => 'Done', 'cancelled' => 'Cancelled'];
    $dayName = fn ($date) => $date->isToday() ? 'Today' : ($date->isTomorrow() ? 'Tomorrow' : ($date->isYesterday() ? 'Yesterday' : $date->format('l, F j')));
    $groups = $siteVisits->getCollection()->groupBy(fn ($visit) => $visit->scheduled_at?->toDateString() ?? 'none');
@endphp

<div class="mb-5 grid grid-cols-2 gap-4 xl:grid-cols-4">
    @foreach([
        ['Today', $stats['today'], $stats['today'] ? 'amber' : null, 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Next 7 Days', $stats['week'], null, 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ['Waiting to Confirm', $stats['unconfirmed'], $stats['unconfirmed'] ? 'amber' : null, 'M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
        ['Done This Month', $stats['completed'], $stats['completed'] ? 'green' : null, 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
    ] as [$tileLabel, $value, $toneName, $icon])
        @include('layouts.partials.stat-tile', ['label' => $tileLabel, 'value' => number_format($value), 'tone' => $toneName, 'icon' => $icon])
    @endforeach
</div>

<div class="overflow-hidden rounded-xl border border-stone-200 bg-white">
    <div class="flex flex-col gap-3 border-b border-stone-100 p-4 sm:flex-row sm:items-center sm:justify-between sm:px-5">
        <nav class="flex gap-1 overflow-x-auto" aria-label="Which visits">
            @foreach(['upcoming' => 'Upcoming', 'past' => 'Past', 'all' => 'All'] as $value => $text)
            <a href="{{ route('agent.site-visits.index', ['when' => $value]) }}" @if($when === $value) aria-current="page" @endif
               class="shrink-0 rounded-full px-4 py-1.5 text-sm font-medium transition {{ $when === $value ? 'bg-teal-700 text-white' : 'text-stone-600 hover:bg-stone-100' }}">{{ $text }}</a>
            @endforeach
        </nav>
        <div class="flex items-center gap-2">
            <form method="GET">
                <input type="hidden" name="when" value="{{ $when }}">
                <label for="visit-status" class="sr-only">Status</label>
                <select id="visit-status" name="status" onchange="this.form.submit()" class="rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-700 focus:outline-none focus:ring-2 focus:ring-teal-400">
                    <option value="">Any status</option>
                    @foreach($label as $value => $text)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $text }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('agent.site-visits.create') }}" class="inline-flex shrink-0 items-center gap-1.5 rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Schedule Visit
            </a>
        </div>
    </div>

    @if($siteVisits->isEmpty())
    <div class="px-6 py-16 text-center">
        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-teal-50 text-teal-700" aria-hidden="true">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
        </span>
        <h2 class="mt-4 font-semibold text-stone-800">{{ $when === 'upcoming' ? 'No upcoming visits' : 'No visits here' }}</h2>
        <p class="mx-auto mt-1 max-w-sm text-sm text-stone-500">Buyers can ask to view a property from its page. Open the viewing request in Inquiries to book it, or schedule one yourself.</p>
        <a href="{{ route('agent.inquiries.index', ['type' => 'viewing']) }}" class="mt-4 inline-flex rounded-lg border border-stone-200 px-4 py-2 text-sm font-semibold text-stone-700 hover:bg-stone-50">See viewing requests</a>
    </div>
    @else
    <div class="divide-y divide-stone-100">
        @foreach($groups as $date => $visits)
        <section>
            <h3 class="bg-stone-50 px-5 py-2 text-xs font-semibold uppercase tracking-wide text-stone-500">{{ $date === 'none' ? 'No date set' : $dayName(\Illuminate\Support\Carbon::parse($date)) }}</h3>
            <ul class="divide-y divide-stone-100">
                @foreach($visits as $visit)
                <li class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center">
                    <div class="w-20 shrink-0">
                        <p class="text-lg font-bold text-stone-800">{{ $visit->scheduled_at?->format('g:i') }}</p>
                        <p class="text-xs font-medium uppercase text-stone-500">{{ $visit->scheduled_at?->format('A') }}</p>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold text-stone-800">{{ $visit->client?->full_name ?? 'Unknown client' }}</p>
                        <p class="truncate text-sm text-stone-600">{{ $visit->property?->name ?? 'Removed listing' }}</p>
                        @if($visit->client?->phone)<a href="tel:{{ preg_replace('/[^\d+]/', '', $visit->client->phone) }}" class="text-xs text-teal-700 hover:underline">{{ $visit->client->phone }}</a>@endif
                    </div>
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $tone[$visit->status] ?? 'bg-stone-100 text-stone-600' }}">{{ $label[$visit->status] ?? ucfirst($visit->status) }}</span>
                        @foreach(array_filter([
                            $visit->status === 'pending' ? ['confirmed', 'Confirm'] : null,
                            in_array($visit->status, ['pending', 'confirmed'], true) && $visit->scheduled_at?->isPast() ? ['completed', 'Done'] : null,
                            in_array($visit->status, ['pending', 'confirmed'], true) ? ['cancelled', 'Cancel'] : null,
                        ]) as [$next, $action])
                        <form method="POST" action="{{ route('agent.site-visits.status', $visit) }}" @if($next === 'cancelled') onsubmit="return confirm('Cancel this site visit?')" @endif>
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="{{ $next }}">
                            <button type="submit" class="rounded-lg border px-3 py-1.5 text-xs font-semibold transition {{ $next === 'cancelled' ? 'border-stone-200 text-stone-600 hover:bg-stone-50' : 'border-teal-200 bg-teal-50 text-teal-800 hover:bg-teal-100' }}">{{ $action }}</button>
                        </form>
                        @endforeach
                        <a href="{{ route('agent.site-visits.edit', $visit) }}" class="rounded-lg border border-stone-200 px-3 py-1.5 text-xs font-semibold text-stone-600 hover:bg-stone-50">Edit</a>
                        <a href="{{ route('agent.site-visits.show', $visit) }}" class="rounded-lg border border-stone-200 px-3 py-1.5 text-xs font-semibold text-stone-600 hover:bg-stone-50" aria-label="Open visit with {{ $visit->client?->full_name }}">Open</a>
                    </div>
                </li>
                @endforeach
            </ul>
        </section>
        @endforeach
    </div>
    @endif
</div>

@if($siteVisits->hasPages())
<div class="mt-4">{{ $siteVisits->links() }}</div>
@endif
@endsection
