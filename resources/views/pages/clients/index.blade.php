@extends('layouts.app')
@section('title', 'Clients')
@section('page-title', 'Client Management')
@section('page-subtitle', 'View and manage all your clients')

@section('content')

<div class="flex flex-col sm:flex-row gap-3 justify-between mb-5">
    <form method="GET" class="flex gap-2">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search clients..." class="border border-stone-200 rounded-lg px-4 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 w-64">
        <button type="submit" class="bg-stone-100 hover:bg-stone-200 text-stone-700 px-4 py-2 rounded-lg text-sm transition">Search</button>
    </form>
    <a href="{{ route('broker.clients.create') }}" class="bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium flex items-center gap-2 transition">
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
        Add Client
    </a>
</div>

@if(session('success'))
<div class="mb-4 p-3 bg-green-50 border border-green-200 text-green-700 rounded-lg text-sm">{{ session('success') }}</div>
@endif

<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
    @forelse($clients as $client)
    <div class="bg-white rounded-xl border border-stone-200 p-5 hover:shadow-md transition">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-11 h-11 bg-amber-100 rounded-full flex items-center justify-center text-amber-700 font-bold text-lg">
                {{ strtoupper(substr($client->first_name, 0, 1)) }}
            </div>
            <div>
                <p class="font-semibold text-stone-800">{{ $client->full_name }}</p>
                <p class="text-xs text-stone-400">{{ $client->email }}</p>
            </div>
        </div>
        <div class="space-y-1.5 text-sm text-stone-500 mb-4">
            <p class="flex items-center gap-2">
                <svg class="w-4 h-4 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                {{ $client->phone ?? '—' }}
            </p>
            <p class="flex items-center gap-2">
                <svg class="w-4 h-4 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                {{ $client->reservations_count }} reservation(s)
            </p>
        </div>
        <div class="flex items-center justify-between pt-3 border-t border-stone-100">
            <span class="px-2 py-1 rounded-full text-xs font-medium
                {{ $client->status === 'active' ? 'bg-green-100 text-green-700' : '' }}
                {{ $client->status === 'inactive' ? 'bg-stone-100 text-stone-500' : '' }}
                {{ $client->status === 'suspended' ? 'bg-red-100 text-red-700' : '' }}">
                {{ ucfirst($client->status) }}
            </span>
            <div class="flex gap-3">
                <button type="button" data-lead-score="{{ $client->id }}" class="text-xs text-blue-600 hover:underline">Score Lead</button>
                <a href="{{ route('broker.clients.show', $client) }}" class="text-xs text-amber-600 hover:underline">View</a>
                <a href="{{ route('broker.clients.edit', $client) }}" class="text-xs text-stone-400 hover:underline">Edit</a>
            </div>
        </div>
    </div>
    @empty
    <div class="col-span-3 py-12 text-center text-stone-400">No clients found.</div>
    @endforelse
</div>

@push('scripts')
<script>
document.querySelectorAll('[data-lead-score]').forEach((button) => {
    button.addEventListener('click', async () => {
        button.disabled = true;
        button.textContent = 'Scoring...';
        const response = await fetch('{{ route('broker.ai.leads') }}', {
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

<div class="mt-5">{{ $clients->links() }}</div>

@endsection
