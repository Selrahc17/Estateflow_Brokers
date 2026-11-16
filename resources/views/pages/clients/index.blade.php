@extends('layouts.app')
@section('title', 'Clients')
@section('page-title', 'Clients')
@section('page-subtitle', 'Buyers you are working with')

@section('content')
@php
    $stages = ['pending' => 'Pending', 'under_review' => 'Under Review', 'approved' => 'Approved', 'rejected' => 'Rejected'];
    $stageTone = [
        'pending' => 'bg-stone-100 text-stone-600',
        'under_review' => 'bg-sky-100 text-sky-800',
        'approved' => 'bg-green-100 text-green-800',
        'rejected' => 'bg-red-100 text-red-700',
    ];
    $field = 'rounded-lg border border-stone-200 bg-white px-3 py-2 text-sm text-stone-700 focus:outline-none focus:ring-2 focus:ring-teal-400';
@endphp

<div class="overflow-hidden rounded-xl border border-stone-200 bg-white">
    {{-- Toolbar --}}
    <div class="space-y-4 border-b border-stone-100 p-4 sm:p-5">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <form method="GET" class="flex flex-1 gap-2">
                @if($stage)<input type="hidden" name="stage" value="{{ $stage }}">@endif
                <label for="client-search" class="sr-only">Search clients</label>
                <div class="relative flex-1">
                    <svg class="pointer-events-none absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    <input id="client-search" type="search" name="search" value="{{ $search }}" placeholder="Search name, email or phone..." class="{{ $field }} w-full pl-9">
                </div>
                <button type="submit" class="rounded-lg bg-stone-800 px-4 py-2 text-sm font-medium text-white hover:bg-stone-900">Search</button>
            </form>
            <a href="{{ route('agent.clients.create') }}" class="inline-flex items-center justify-center gap-1.5 rounded-lg bg-teal-700 px-4 py-2 text-sm font-semibold text-white hover:bg-teal-800">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Add Client
            </a>
        </div>
        <nav class="-mx-1 flex gap-1 overflow-x-auto px-1" aria-label="Filter by status">
            @foreach(['' => 'All'] + $stages as $value => $text)
            @php($active = (string) $stage === (string) $value)
            <a href="{{ route('agent.clients.index', array_filter(['stage' => $value ?: null, 'search' => $search ?: null])) }}" @if($active) aria-current="page" @endif
               class="inline-flex shrink-0 items-center gap-2 rounded-full px-3.5 py-1.5 text-sm font-medium transition {{ $active ? 'bg-teal-700 text-white' : 'text-stone-600 hover:bg-stone-100' }}">
                {{ $text }}
                <span class="rounded-full px-1.5 text-xs {{ $active ? 'bg-white/20' : 'bg-stone-100 text-stone-500' }}">{{ $value === '' ? $stageCounts->sum() : (int) $stageCounts->get($value, 0) }}</span>
            </a>
            @endforeach
        </nav>
    </div>

    @if($clients->isEmpty())
    <div class="px-6 py-16 text-center">
        <span class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-teal-50 text-teal-700" aria-hidden="true">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
        </span>
        @if($search || $stage)
        <h2 class="mt-4 font-semibold text-stone-800">No clients match</h2>
        <p class="mt-1 text-sm text-stone-500"><a href="{{ route('agent.clients.index') }}" class="font-medium text-teal-700 hover:underline">Clear the filters</a> to see all clients.</p>
        @else
        <h2 class="mt-4 font-semibold text-stone-800">No clients yet</h2>
        <p class="mx-auto mt-1 max-w-sm text-sm text-stone-500">Open an inquiry and choose <span class="font-medium">Add as Client</span>, or add a walk-in buyer yourself.</p>
        @endif
    </div>
    @else
    <ul class="divide-y divide-stone-100">
        @foreach($clients as $client)
        <li class="flex flex-col gap-3 px-4 py-4 sm:flex-row sm:items-center sm:px-5">
            <a href="{{ route('agent.clients.show', $client) }}" class="flex min-w-0 flex-1 items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-teal-100 text-sm font-bold text-teal-800" aria-hidden="true">{{ mb_strtoupper(mb_substr($client->first_name, 0, 1) . mb_substr((string) $client->last_name, 0, 1)) }}</span>
                <span class="min-w-0">
                    <span class="block truncate font-semibold text-stone-800">{{ $client->full_name }}</span>
                    <span class="block truncate text-xs text-stone-500">{{ $client->email }}{{ $client->phone ? ' · ' . $client->phone : '' }}</span>
                </span>
            </a>
            <div class="flex flex-wrap items-center gap-x-4 gap-y-2 text-xs text-stone-500">
                <span class="rounded-full px-2.5 py-1 font-semibold {{ $stageTone[$client->qualification_status] ?? 'bg-stone-100 text-stone-600' }}">{{ $stages[$client->qualification_status] ?? ucfirst((string) $client->qualification_status) }}</span>
                <span title="Reservations">{{ $client->reservations_count }} {{ $client->reservations_count === 1 ? 'reservation' : 'reservations' }}</span>
                <span title="Site visits">{{ $client->site_visits_count }} {{ $client->site_visits_count === 1 ? 'visit' : 'visits' }}</span>
                <span>Added {{ $client->created_at->format('M j, Y') }}</span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" data-lead-score="{{ $client->id }}" class="rounded-lg border border-sky-200 bg-sky-50 px-3 py-1.5 text-xs font-semibold text-sky-800 hover:bg-sky-100">Score Lead</button>
                @if($client->phone)
                <a href="tel:{{ preg_replace('/[^\d+]/', '', $client->phone) }}" class="rounded-lg border border-stone-200 px-3 py-1.5 text-xs font-semibold text-stone-600 hover:bg-stone-50">Call</a>
                @endif
                <a href="{{ route('agent.clients.edit', $client) }}" class="rounded-lg border border-stone-200 px-3 py-1.5 text-xs font-semibold text-stone-600 hover:bg-stone-50">Edit</a>
                <a href="{{ route('agent.clients.show', $client) }}" class="rounded-lg bg-teal-700 px-3 py-1.5 text-xs font-semibold text-white hover:bg-teal-800">Open</a>
            </div>
        </li>
        @endforeach
    </ul>
    @endif
</div>

@if($clients->hasPages())
<div class="mt-4">{{ $clients->links() }}</div>
@endif

@push('scripts')
<script>
document.querySelectorAll('[data-lead-score]').forEach((button) => {
    button.addEventListener('click', async () => {
        button.disabled = true;
        button.textContent = 'Scoring...';
        const response = await fetch('{{ route('agent.ai.leads') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ client_id: button.dataset.leadScore })
        });
        const result = await response.json();
        button.textContent = response.ok ? `${result.priority} (${result.score})` : 'Unavailable';
        button.disabled = false;
    });
});
</script>
@endpush
@endsection
