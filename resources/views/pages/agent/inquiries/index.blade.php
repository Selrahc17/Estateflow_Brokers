@extends('layouts.app')
@section('title', 'Inquiries')
@section('page-title', 'Inquiries')
@section('page-subtitle', 'Questions and viewing requests from buyers')

@section('content')
@use('App\Models\Inquiry')
@php
    $statusTone = [
        'new' => 'bg-sky-50 text-sky-800 border-sky-200',
        'contacted' => 'bg-amber-50 text-amber-800 border-amber-200',
        'site_visit_scheduled' => 'bg-violet-50 text-violet-800 border-violet-200',
        'negotiating' => 'bg-orange-50 text-orange-800 border-orange-200',
        'closed' => 'bg-green-50 text-green-800 border-green-200',
        'lost' => 'bg-stone-100 text-stone-600 border-stone-200',
    ];
    $field = 'rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-700 focus:outline-none focus:ring-2 focus:ring-teal-400';
    $filters = array_filter(['q' => $search ?: null, 'type' => $type, 'status' => $status]);
    $initials = fn (Inquiry $inquiry) => collect(preg_split('/\s+/', trim($inquiry->contact_label)))->filter()->take(2)->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))->join('') ?: '?';
@endphp

@if(session('success'))
<div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700" role="status">{{ session('success') }}</div>
@endif

{{-- Summary --}}
<div class="mb-6 grid grid-cols-2 gap-4 xl:grid-cols-4">
    @foreach([
        ['All Inquiries', number_format($stats['total']), null, 'M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z'],
        ['New, Not Yet Answered', number_format($stats['new']), $stats['new'] ? 'amber' : null, 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
        ['Open Viewing Requests', number_format($stats['viewings']), $stats['viewings'] ? 'amber' : null, 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z'],
        ['This Month', number_format($stats['this_month']), null, 'M13 7h8m0 0v8m0-8l-8 8-4-4-6 6'],
    ] as [$label, $value, $tone, $icon])
        @include('layouts.partials.stat-tile', compact('label', 'value', 'tone', 'icon'))
    @endforeach
</div>

<div class="overflow-hidden rounded-xl border border-stone-200 bg-white shadow-sm">
    {{-- Toolbar --}}
    <div class="space-y-4 border-b border-stone-100 p-4 sm:p-5">
        <form method="GET" class="flex flex-col gap-2 sm:flex-row">
            @if($status)<input type="hidden" name="status" value="{{ $status }}">@endif
            <label for="inquiry-search" class="sr-only">Search</label>
            <div class="relative flex-1">
                <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                <input id="inquiry-search" type="search" name="q" value="{{ $search }}" placeholder="Search buyer, email, phone or property..." class="{{ $field }} w-full pl-9">
            </div>
            <label for="inquiry-type" class="sr-only">Type</label>
            <select id="inquiry-type" name="type" onchange="this.form.submit()" class="{{ $field }} sm:w-48">
                <option value="">All types</option>
                <option value="inquiry" @selected($type === 'inquiry')>Inquiries</option>
                <option value="viewing" @selected($type === 'viewing')>Viewing requests</option>
            </select>
            <button type="submit" class="rounded-lg bg-teal-700 px-4 py-2 text-sm font-medium text-white hover:bg-teal-800">Search</button>
            @if($filters)
            <a href="{{ route('agent.inquiries.index') }}" class="rounded-lg px-3 py-2 text-center text-sm text-stone-500 hover:text-stone-800">Clear</a>
            @endif
        </form>

        <nav class="-mx-1 flex gap-1 overflow-x-auto px-1 pb-1" aria-label="Filter by status">
            @foreach(['' => 'All'] + Inquiry::STATUSES as $value => $label)
            <a href="{{ route('agent.inquiries.index', array_filter(['q' => $search ?: null, 'type' => $type, 'status' => $value ?: null])) }}"
               @if((string) $status === (string) $value) aria-current="page" @endif
               class="inline-flex shrink-0 items-center gap-2 rounded-full px-3.5 py-1.5 text-sm font-medium transition {{ (string) $status === (string) $value ? 'bg-teal-700 text-white' : 'text-stone-600 hover:bg-stone-100' }}">
                {{ $label }}
                <span class="rounded-full px-1.5 text-xs {{ (string) $status === (string) $value ? 'bg-white/20' : 'bg-stone-100 text-stone-500' }}">{{ $value === '' ? $statusCounts->sum() : (int) $statusCounts->get($value, 0) }}</span>
            </a>
            @endforeach
        </nav>
    </div>

    @if($inquiries->isNotEmpty())
    {{-- Desktop table --}}
    <div class="hidden overflow-x-auto lg:block">
        <table class="w-full text-sm">
            <thead class="bg-stone-50 text-left text-xs font-semibold uppercase tracking-wide text-stone-500">
                <tr>
                    <th class="px-3 py-3">Buyer</th>
                    <th class="px-3 py-3">Property</th>
                    <th class="px-3 py-3">Request</th>
                    <th class="px-3 py-3">Contact</th>
                    <th class="px-3 py-3">Status</th>
                    <th class="px-3 py-3">Received</th>
                    <th class="px-3 py-3"><span class="sr-only">Open</span></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100">
                @foreach($inquiries as $inquiry)
                <tr class="align-top transition hover:bg-stone-50/70 {{ $inquiry->status === 'new' ? 'bg-sky-50/30' : '' }}">
                    <td class="px-3 py-4">
                        <div class="flex items-center gap-3">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-teal-100 text-xs font-bold text-teal-800" aria-hidden="true">{{ $initials($inquiry) }}</span>
                            <div class="min-w-0">
                                <p class="font-semibold text-stone-800">
                                    {{ $inquiry->contact_label }}
                                    @if($inquiry->status === 'new')<span class="ml-1 inline-block h-2 w-2 rounded-full bg-sky-500 align-middle" title="Not answered yet"></span>@endif
                                </p>
                                <p class="truncate text-xs text-stone-500">{{ $inquiry->email }}</p>
                            </div>
                        </div>
                    </td>
                    <td class="min-w-[180px] max-w-[250px] px-3 py-4">
                        @if($inquiry->property)
                        <a href="{{ route('agent.properties.show', $inquiry->property) }}" class="font-medium text-stone-700 hover:text-teal-700">{{ $inquiry->property->name }}</a>
                        @else
                        <span class="text-stone-400">Removed listing</span>
                        @endif
                        <p class="mt-1 line-clamp-1 text-xs text-stone-500" title="{{ $inquiry->message }}">{{ $inquiry->message }}</p>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4">
                        @if($inquiry->isViewingRequest())
                        <span class="inline-flex items-center gap-1 rounded-full border border-violet-200 bg-violet-50 px-2.5 py-0.5 text-xs font-semibold text-violet-800">
                            <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            Viewing
                        </span>
                        @if($inquiry->preferred_viewing_at)
                        <p class="mt-1 text-xs font-medium text-stone-700">{{ $inquiry->preferred_viewing_at->format('D, M j · g:i A') }}</p>
                        @endif
                        @else
                        <span class="inline-flex rounded-full border border-stone-200 bg-white px-2.5 py-0.5 text-xs font-semibold text-stone-600">Inquiry</span>
                        @endif
                    </td>
                    <td class="whitespace-nowrap px-3 py-4">
                        <div class="flex items-center gap-1.5">
                            @if($inquiry->phone)
                            <a href="tel:{{ preg_replace('/[^\d+]/', '', $inquiry->phone) }}" class="rounded-lg border border-stone-200 p-1.5 text-stone-500 hover:border-teal-300 hover:text-teal-700" aria-label="Call {{ $inquiry->contact_label }}" title="{{ $inquiry->phone }}">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </a>
                            @endif
                            <a href="mailto:{{ $inquiry->email }}" class="rounded-lg border border-stone-200 p-1.5 text-stone-500 hover:border-teal-300 hover:text-teal-700" aria-label="Email {{ $inquiry->contact_label }}" title="{{ $inquiry->email }}">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </a>
                            @if($inquiry->facebook_url)
                            <a href="{{ $inquiry->facebook_url }}" target="_blank" rel="noopener noreferrer" class="rounded-lg border border-stone-200 p-1.5 text-stone-500 hover:border-teal-300 hover:text-teal-700" aria-label="Facebook profile of {{ $inquiry->contact_label }}">
                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M22 12a10 10 0 10-11.56 9.88v-6.99H7.9V12h2.54V9.8c0-2.5 1.49-3.89 3.78-3.89 1.09 0 2.24.2 2.24.2v2.46h-1.26c-1.24 0-1.63.77-1.63 1.56V12h2.78l-.44 2.89h-2.34v6.99A10 10 0 0022 12z"/></svg>
                            </a>
                            @endif
                        </div>
                        <p class="mt-1 text-xs text-stone-500">{{ $inquiry->phone }}</p>
                    </td>
                    <td class="px-3 py-4">
                        <form action="{{ route('agent.inquiries.status', $inquiry) }}" method="POST">
                            @csrf @method('PATCH')
                            <label class="sr-only" for="status-{{ $inquiry->id }}">Status for {{ $inquiry->contact_label }}</label>
                            <select id="status-{{ $inquiry->id }}" name="status" onchange="this.form.submit()" class="rounded-full border px-2.5 py-1 text-xs font-semibold focus:outline-none focus:ring-2 focus:ring-teal-400 {{ $statusTone[$inquiry->status] ?? '' }}">
                                @foreach(Inquiry::STATUSES as $value => $label)
                                <option value="{{ $value }}" @selected($inquiry->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </form>
                    </td>
                    <td class="whitespace-nowrap px-3 py-4">
                        <p class="text-stone-700">{{ $inquiry->created_at->format('M j, Y') }}</p>
                        <p class="text-xs text-stone-500">{{ $inquiry->created_at->diffForHumans() }}</p>
                    </td>
                    <td class="px-3 py-4 pr-5 text-right">
                        <a href="{{ route('agent.inquiries.show', $inquiry) }}" class="inline-flex h-8 w-8 items-center justify-center rounded-lg border border-stone-200 text-stone-500 hover:border-teal-300 hover:text-teal-700" aria-label="Open inquiry from {{ $inquiry->contact_label }}" title="Open"><svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg></a>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Phone and tablet cards --}}
    <ul class="divide-y divide-stone-100 lg:hidden">
        @foreach($inquiries as $inquiry)
        <li class="p-4 {{ $inquiry->status === 'new' ? 'bg-sky-50/30' : '' }}">
            <div class="flex items-start justify-between gap-3">
                <a href="{{ route('agent.inquiries.show', $inquiry) }}" class="flex min-w-0 items-center gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-teal-100 text-xs font-bold text-teal-800" aria-hidden="true">{{ $initials($inquiry) }}</span>
                    <span class="min-w-0">
                        <span class="block truncate font-semibold text-stone-800">{{ $inquiry->contact_label }}</span>
                        <span class="block truncate text-xs text-stone-500">{{ $inquiry->property?->name ?? 'Removed listing' }}</span>
                    </span>
                </a>
                <span class="shrink-0 text-xs text-stone-500">{{ $inquiry->created_at->diffForHumans(short: true) }}</span>
            </div>
            <div class="mt-2 flex flex-wrap items-center gap-2">
                @if($inquiry->isViewingRequest())
                <span class="rounded-full border border-violet-200 bg-violet-50 px-2.5 py-0.5 text-xs font-semibold text-violet-800">Viewing{{ $inquiry->preferred_viewing_at ? ' · ' . $inquiry->preferred_viewing_at->format('M j, g:i A') : '' }}</span>
                @else
                <span class="rounded-full border border-stone-200 px-2.5 py-0.5 text-xs font-semibold text-stone-600">Inquiry</span>
                @endif
                <span class="rounded-full border px-2.5 py-0.5 text-xs font-semibold {{ $statusTone[$inquiry->status] ?? '' }}">{{ $inquiry->status_label }}</span>
            </div>
            <p class="mt-2 line-clamp-2 text-sm text-stone-600">{{ $inquiry->message }}</p>
            <div class="mt-3 flex gap-2">
                @if($inquiry->phone)
                <a href="tel:{{ preg_replace('/[^\d+]/', '', $inquiry->phone) }}" class="flex-1 rounded-lg border border-stone-200 py-2 text-center text-xs font-semibold text-stone-700">Call</a>
                @endif
                <a href="mailto:{{ $inquiry->email }}" class="flex-1 rounded-lg border border-stone-200 py-2 text-center text-xs font-semibold text-stone-700">Email</a>
                <a href="{{ route('agent.inquiries.show', $inquiry) }}" class="flex-1 rounded-lg bg-teal-700 py-2 text-center text-xs font-semibold text-white">Open</a>
            </div>
        </li>
        @endforeach
    </ul>
    @else
    <div class="px-6 py-16 text-center">
        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-stone-100 text-stone-400" aria-hidden="true">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
        </span>
        @if($filters)
        <h3 class="mt-4 font-semibold text-stone-800">No inquiries match these filters</h3>
        <p class="mt-1 text-sm text-stone-500"><a href="{{ route('agent.inquiries.index') }}" class="font-medium text-teal-700 hover:underline">Clear the filters</a> to see everything.</p>
        @else
        <h3 class="mt-4 font-semibold text-stone-800">No inquiries yet</h3>
        <p class="mt-1 text-sm text-stone-500">When buyers ask about your listings or request a viewing, they appear here.</p>
        @endif
    </div>
    @endif
</div>

@if($inquiries->hasPages())
<div class="mt-4">{{ $inquiries->links() }}</div>
@endif
@endsection
