@extends('layouts.app')
@section('title', 'Inquiry Details')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <a href="{{ route('client.account.inquiries') }}" class="text-amber-600 hover:text-amber-700 text-sm font-medium mb-2 inline-flex items-center gap-1">
                ← Back to Inquiries
            </a>
            <h1 class="text-3xl font-bold text-stone-800">Inquiry Details</h1>
        </div>
        <div class="text-right">
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
            <span class="inline-block px-4 py-2 rounded-full text-sm font-semibold {{ $statusColors[$inquiry->status] ?? 'bg-stone-50 text-stone-700' }}">
                {{ ucfirst(str_replace('_', ' ', $inquiry->status)) }}
            </span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Property & Inquiry Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Property Info --}}
            <div class="bg-white rounded-lg border border-stone-200 p-6">
                <h2 class="text-lg font-semibold text-stone-800 mb-4">Property Information</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-stone-400 font-semibold uppercase">Property Name</p>
                        <a href="{{ route('client.property.show', $inquiry->property->slug) }}" class="text-amber-600 hover:text-amber-700 font-semibold">
                            {{ $inquiry->property->name }}
                        </a>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-stone-400 font-semibold uppercase">Location</p>
                            <p class="text-sm font-medium text-stone-700 mt-1">{{ $inquiry->property->city }}, {{ $inquiry->property->province }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-stone-400 font-semibold uppercase">Type</p>
                            <p class="text-sm font-medium text-stone-700 mt-1">{{ ucfirst($inquiry->property->type) }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-stone-400 font-semibold uppercase">Price</p>
                            <p class="text-lg font-bold text-amber-600 mt-1">₱{{ number_format($inquiry->property->price, 0) }}</p>
                        </div>
                        @if($inquiry->lot)
                        <div>
                            <p class="text-xs text-stone-400 font-semibold uppercase">Lot</p>
                            <p class="text-sm font-medium text-stone-700 mt-1">{{ $inquiry->lot->name }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Your Message --}}
            <div class="bg-white rounded-lg border border-stone-200 p-6">
                <h2 class="text-lg font-semibold text-stone-800 mb-4">Your Inquiry Message</h2>
                <div class="bg-stone-50 rounded-lg p-4 border border-stone-100">
                    <p class="text-sm text-stone-700 whitespace-pre-wrap">{{ $inquiry->message }}</p>
                </div>
                <div class="mt-4 pt-4 border-t border-stone-100 grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-stone-400 mb-1">Your Email</p>
                        <p class="text-sm font-medium text-stone-700">{{ $inquiry->email }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-stone-400 mb-1">Your Phone</p>
                        <p class="text-sm font-medium text-stone-700">{{ $inquiry->phone }}</p>
                    </div>
                </div>
            </div>

            {{-- Timeline --}}
            <div class="bg-white rounded-lg border border-stone-200 p-6">
                <h2 class="text-lg font-semibold text-stone-800 mb-4">Inquiry Timeline</h2>
                <div class="space-y-3">
                    <div class="flex gap-3">
                        <div class="text-sm text-stone-400 w-32 shrink-0">{{ $inquiry->created_at->format('M d, Y H:i') }}</div>
                        <div class="flex-1 pb-3 border-l-2 border-amber-300 pl-4">
                            <p class="font-semibold text-stone-700">Inquiry Submitted</p>
                            <p class="text-xs text-stone-400">Your inquiry was sent to the broker</p>
                        </div>
                    </div>
                    @if($inquiry->status !== 'new')
                    <div class="flex gap-3">
                        <div class="text-sm text-stone-400 w-32 shrink-0">Pending</div>
                        <div class="flex-1 pb-3 border-l-2 border-stone-300 pl-4">
                            <p class="font-semibold text-stone-700">{{ ucfirst(str_replace('_', ' ', $inquiry->status)) }}</p>
                            <p class="text-xs text-stone-400">Current status of your inquiry</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right: Broker Info --}}
        <div class="space-y-6">
            <div class="bg-white rounded-lg border border-stone-200 p-6 sticky top-28">
                <h2 class="text-lg font-semibold text-stone-800 mb-4">Broker Contact</h2>
                
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-amber-600 rounded-full flex items-center justify-center text-white text-lg font-bold">
                        {{ substr($inquiry->broker->name, 0, 1) }}
                    </div>
                    <div>
                        <p class="font-semibold text-stone-800">{{ $inquiry->broker->name }}</p>
                        <p class="text-xs text-stone-400">Licensed Broker</p>
                    </div>
                </div>

                <div class="space-y-2 py-4 border-y border-stone-100">
                    <p class="text-xs text-stone-400 font-semibold uppercase">Status</p>
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 bg-green-500 rounded-full"></div>
                        <p class="text-sm text-stone-700">Active Broker</p>
                    </div>
                </div>

                <div class="mt-4 pt-4 space-y-2">
                    <a href="{{ route('client.account.chat') }}" class="block w-full text-center bg-amber-600 hover:bg-amber-700 text-white py-2 rounded-lg text-sm font-semibold transition">
                        Message Broker
                    </a>
                    <a href="{{ route('client.properties') }}" class="block w-full text-center border border-stone-200 text-stone-700 hover:bg-stone-50 py-2 rounded-lg text-sm font-medium transition">
                        View More Properties
                    </a>
                </div>
            </div>

            {{-- Help Section --}}
            <div class="bg-amber-50 rounded-lg border border-amber-200 p-4">
                <p class="text-xs font-semibold text-amber-900 uppercase mb-2">💡 Next Steps</p>
                <ul class="text-xs text-amber-900 space-y-1.5">
                    <li class="flex gap-2">
                        <span>✓</span>
                        <span>Broker will review your inquiry</span>
                    </li>
                    <li class="flex gap-2">
                        <span>✓</span>
                        <span>You'll receive contact updates</span>
                    </li>
                    <li class="flex gap-2">
                        <span>✓</span>
                        <span>Schedule a site visit</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
