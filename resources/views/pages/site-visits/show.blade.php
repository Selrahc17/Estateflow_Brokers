@extends('layouts.app')
@section('title', 'Site Visit Details')
@section('page-title', 'Site Visit Details')
@section('page-subtitle', 'View and manage this site visit')

@section('content')

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-6">
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="text-lg font-semibold text-stone-800">{{ $siteVisit->client?->full_name ?? '—' }}</h3>
                    <p class="text-sm text-stone-500">{{ $siteVisit->property?->name ?? '—' }}</p>
                </div>
                <span class="px-3 py-1 rounded-full text-xs font-medium {{ match($siteVisit->status) {
                    'pending' => 'bg-yellow-100 text-yellow-700',
                    'confirmed' => 'bg-blue-100 text-blue-700',
                    'completed' => 'bg-green-100 text-green-700',
                    'cancelled' => 'bg-red-100 text-red-600',
                    default => 'bg-stone-100 text-stone-600',
                } }}">
                    {{ ucfirst($siteVisit->status) }}
                </span>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                <div>
                    <p class="text-stone-400 mb-1">Scheduled At</p>
                    <p class="font-medium text-stone-800">{{ $siteVisit->scheduled_at?->format('M d, Y h:i A') ?? '—' }}</p>
                </div>
                <div>
                    <p class="text-stone-400 mb-1">Inquiry</p>
                    <p class="font-medium text-stone-800">{{ $siteVisit->inquiry?->client?->full_name ?? '—' }}</p>
                </div>
            </div>

            @if($siteVisit->notes)
                <div class="border-t border-stone-100 pt-4">
                    <p class="text-stone-400 text-sm mb-2">Notes</p>
                    <p class="text-stone-700">{{ $siteVisit->notes }}</p>
                </div>
            @endif
        </div>

        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <h3 class="font-semibold text-stone-800 mb-4">Update Status</h3>
            <div class="flex flex-wrap gap-2">
                @foreach(['pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'] as $key => $label)
                    <form action="{{ route('agent.site-visits.status', $siteVisit) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="{{ $key }}">
                        <button type="submit" class="px-3 py-1.5 text-sm rounded-lg font-medium transition {{ $siteVisit->status === $key ? (match($key) {
                            'pending' => 'bg-yellow-600 text-white',
                            'confirmed' => 'bg-blue-600 text-white',
                            'completed' => 'bg-green-600 text-white',
                            'cancelled' => 'bg-red-600 text-white',
                            default => 'bg-stone-600 text-white',
                        }) : 'bg-stone-100 text-stone-600 hover:bg-stone-200' }}">
                            {{ $label }}
                        </button>
                    </form>
                @endforeach
            </div>
        </div>
    </div>

    <div class="space-y-6">
        <div class="bg-white rounded-xl border border-stone-200 p-6">
            <h3 class="font-semibold text-stone-800 mb-4">Actions</h3>
            <div class="space-y-2">
                <a href="{{ route('agent.site-visits.edit', $siteVisit) }}" class="flex items-center gap-2 px-4 py-2.5 border border-stone-200 rounded-lg text-sm text-stone-600 hover:bg-stone-50 transition">
                    Edit Site Visit
                </a>
                <a href="{{ route('agent.site-visits.index') }}" class="flex items-center gap-2 px-4 py-2.5 border border-stone-200 rounded-lg text-sm text-stone-600 hover:bg-stone-50 transition">
                    Back to All Site Visits
                </a>
            </div>
        </div>
    </div>
</div>

@endsection