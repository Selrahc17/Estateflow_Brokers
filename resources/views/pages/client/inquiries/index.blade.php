@extends('layouts.app')
@section('title', 'My Inquiries')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
    {{-- Header --}}
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-stone-800">My Inquiries</h1>
        <p class="text-stone-400 mt-1">Track all your property inquiries</p>
    </div>

    @if($inquiries->count() > 0)
        <div class="space-y-4">
            @foreach($inquiries as $inquiry)
                <div class="bg-white rounded-lg border border-stone-200 p-6 hover:shadow-md transition">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-start">
                        <div>
                            <h3 class="font-semibold text-stone-800">{{ $inquiry->property->name }}</h3>
                            <p class="text-xs text-stone-400 mt-1 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                {{ $inquiry->property->city }}, {{ $inquiry->property->province }}
                            </p>
                        </div>

                        <div>
                            <p class="text-xs text-stone-400 font-semibold uppercase">Broker</p>
                            <p class="text-sm font-medium text-stone-700 mt-1">{{ $inquiry->broker->name }}</p>
                        </div>

                        <div>
                            <p class="text-xs text-stone-400 font-semibold uppercase">Status</p>
                            <div class="mt-1">
                                @php
                                    $statusColors = [
                                        'new' => 'bg-blue-50 text-blue-700',
                                        'contacted' => 'bg-green-50 text-green-700',
                                        'site_visit_scheduled' => 'bg-purple-50 text-purple-700',
                                        'negotiating' => 'bg-orange-50 text-orange-700',
                                        'closed' => 'bg-emerald-50 text-emerald-700',
                                        'lost' => 'bg-red-50 text-red-700',
                                    ];
                                @endphp
                                <span class="inline-block px-3 py-1 rounded-full text-xs font-semibold {{ $statusColors[$inquiry->status] ?? 'bg-stone-50 text-stone-700' }}">
                                    {{ ucfirst(str_replace('_', ' ', $inquiry->status)) }}
                                </span>
                            </div>
                        </div>

                        <div class="text-right">
                            <p class="text-xs text-stone-400 mb-2">Sent on</p>
                            <p class="text-sm font-medium text-stone-700">{{ $inquiry->created_at->format('M d, Y') }}</p>
                            <a href="{{ route('client.account.inquiries.show', $inquiry->id) }}" class="text-xs text-teal-700 hover:underline mt-2 inline-block">
                                View Details →
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        <div class="mt-8">
            {{ $inquiries->links() }}
        </div>
    @else
        <div class="text-center py-12">
            <div class="text-5xl mb-3">📋</div>
            <h3 class="text-xl font-semibold text-stone-700 mb-2">No inquiries yet</h3>
            <p class="text-stone-400 mb-6">Start sending inquiries to properties you're interested in!</p>
            <a href="{{ route('client.properties') }}" class="inline-block bg-teal-700 hover:bg-teal-800 text-white px-6 py-3 rounded-xl font-semibold transition">
                Browse Properties
            </a>
        </div>
    @endif
</div>
@endsection
