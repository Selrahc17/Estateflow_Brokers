@extends('layouts.public')
@section('title', 'My Appointments')

@section('content')
<div class="bg-gradient-to-r from-[#112E3B] to-[#1A6B79] text-white">
    <div class="max-w-6xl mx-auto px-6 py-10">
        <p class="text-teal-500 text-xs uppercase tracking-widest font-semibold mb-1">My Appointments</p>
        <h1 class="text-2xl font-bold">Site Visit Requests</h1>
    </div>
</div>

<div class="max-w-6xl mx-auto px-6 py-8 space-y-5">
    @forelse($siteVisits as $siteVisit)
    <div class="bg-white rounded-2xl border border-stone-200 p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <p class="font-semibold text-stone-800">{{ $siteVisit->property?->name ?? 'Property unavailable' }}</p>
            <p class="text-sm text-stone-500 mt-1">{{ $siteVisit->scheduled_at->format('M d, Y g:i A') }}</p>
            <p class="text-xs text-stone-400 mt-1">Broker: {{ $siteVisit->broker?->name ?? '—' }}</p>
        </div>
        <span class="px-3 py-1 rounded-full text-xs font-semibold self-start sm:self-auto
            {{ $siteVisit->status === 'confirmed' ? 'bg-green-100 text-green-700' : '' }}
            {{ $siteVisit->status === 'pending' ? 'bg-yellow-100 text-yellow-700' : '' }}
            {{ $siteVisit->status === 'completed' ? 'bg-blue-100 text-blue-700' : '' }}
            {{ $siteVisit->status === 'cancelled' ? 'bg-red-100 text-red-600' : '' }}">
            {{ ucfirst($siteVisit->status) }}
        </span>
        @if(in_array($siteVisit->status, ['pending', 'confirmed'], true))
        <div class="flex flex-wrap gap-2 sm:justify-end">
            <form action="{{ route('client.account.site-visits.reschedule', $siteVisit) }}" method="POST" class="flex items-center gap-2">
                @csrf
                @method('PATCH')
                <input type="datetime-local" name="scheduled_at" required min="{{ now()->format('Y-m-d\\TH:i') }}" class="border border-stone-200 rounded-lg px-2 py-1 text-xs">
                <button type="submit" class="text-xs text-teal-800 hover:underline">Reschedule</button>
            </form>
            <form action="{{ route('client.account.site-visits.cancel', $siteVisit) }}" method="POST">
                @csrf
                @method('PATCH')
                <button type="submit" class="text-xs text-red-600 hover:underline">Cancel</button>
            </form>
        </div>
        @endif
    </div>
    @empty
    <div class="bg-white rounded-2xl border border-stone-200 py-16 text-center text-stone-400">No appointment requests yet.</div>
    @endforelse

    <div>{{ $siteVisits->links() }}</div>
</div>
@endsection
