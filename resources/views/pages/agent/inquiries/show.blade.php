@extends('layouts.app')
@section('title', 'Inquiry Details')

@section('content')
<div class="max-w-4xl mx-auto px-4 sm:px-6 py-8">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <a href="{{ route('agent.inquiries.index') }}" class="text-teal-700 hover:text-teal-800 text-sm font-medium mb-2 inline-flex items-center gap-1">
                ← Back to Inquiries
            </a>
            <h1 class="text-3xl font-bold text-stone-800">Inquiry from {{ $inquiry->user?->name ?? 'Guest' }}</h1>
        </div>
        <div>
            <form action="{{ route('agent.inquiries.status', $inquiry->id) }}" method="POST" class="inline">
                @csrf
                @method('PATCH')
                <div class="flex gap-2">
                    <select name="status" onchange="this.form.submit()" 
                            class="px-3 py-2 rounded-lg border border-stone-200 focus:outline-none focus:ring-2 focus:ring-teal-400 font-medium">
                        @php
                            $statuses = ['new' => 'New', 'contacted' => 'Contacted', 'site_visit_scheduled' => 'Site Visit Scheduled', 'negotiating' => 'Negotiating', 'closed' => 'Closed', 'lost' => 'Lost'];
                        @endphp
                        @foreach($statuses as $value => $label)
                            <option value="{{ $value }}" {{ $inquiry->status === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Inquiry & Message --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Buyer Information --}}
            <div class="bg-white rounded-lg border border-stone-200 p-6">
                <h2 class="text-lg font-semibold text-stone-800 mb-4">Buyer Information</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-stone-400 font-semibold uppercase">Full Name</p>
                        <p class="text-lg font-semibold text-stone-800 mt-1">{{ $inquiry->user?->name ?? 'Guest' }}</p>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-stone-400 font-semibold uppercase">Email</p>
                            <a href="mailto:{{ $inquiry->email }}" class="text-teal-700 hover:text-teal-800 font-medium mt-1">
                                {{ $inquiry->email }}
                            </a>
                        </div>
                        <div>
                            <p class="text-xs text-stone-400 font-semibold uppercase">Phone</p>
                            <a href="tel:{{ $inquiry->phone }}" class="text-teal-700 hover:text-teal-800 font-medium mt-1">
                                {{ $inquiry->phone }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Property Information --}}
            <div class="bg-white rounded-lg border border-stone-200 p-6">
                <h2 class="text-lg font-semibold text-stone-800 mb-4">Property Details</h2>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-stone-400 font-semibold uppercase">Property Name</p>
                        <a href="{{ route('agent.properties.show', $inquiry->property->id) }}" class="text-teal-700 hover:text-teal-800 font-semibold text-lg mt-1">
                            {{ $inquiry->property->name }}
                        </a>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-stone-400 font-semibold uppercase">Type</p>
                            <p class="text-sm font-medium text-stone-700 mt-1">{{ ucfirst($inquiry->property->type) }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-stone-400 font-semibold uppercase">Location</p>
                            <p class="text-sm font-medium text-stone-700 mt-1">{{ $inquiry->property->city }}, {{ $inquiry->property->province }}</p>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-xs text-stone-400 font-semibold uppercase">Price</p>
                            <p class="text-lg font-bold text-teal-700 mt-1">₱{{ number_format($inquiry->property->price, 0) }}</p>
                        </div>
                        @if($inquiry->lot)
                        <div>
                            <p class="text-xs text-stone-400 font-semibold uppercase">Specific Lot</p>
                            <p class="text-sm font-medium text-stone-700 mt-1">{{ $inquiry->lot->name }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Inquiry Message --}}
            <div class="bg-white rounded-lg border border-stone-200 p-6">
                <h2 class="text-lg font-semibold text-stone-800 mb-4">Buyer's Inquiry Message</h2>
                <div class="bg-stone-50 rounded-lg p-4 border border-stone-100">
                    <p class="text-sm text-stone-700 whitespace-pre-wrap leading-relaxed">{{ $inquiry->message }}</p>
                </div>
                <p class="text-xs text-stone-400 mt-3">Sent on {{ $inquiry->created_at->format('F d, Y \a\t g:i A') }}</p>
            </div>

            {{-- Quick Actions --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <a href="mailto:{{ $inquiry->email }}?subject=Re:%20Inquiry%20for%20{{ urlencode($inquiry->property->name) }}" 
                   class="bg-teal-700 hover:bg-teal-800 text-white py-3 rounded-lg font-semibold text-center transition">
                    Send Email Reply
                </a>
                <a href="tel:{{ $inquiry->phone }}"
                   class="border border-teal-700 text-teal-700 hover:bg-teal-50 py-3 rounded-lg font-semibold text-center transition">
                    Call Buyer
                </a>
                @if($inquiry->user)
                <a href="{{ route('agent.messages.index', ['contact' => $inquiry->user->id]) }}"
                   class="border border-teal-700 text-teal-700 hover:bg-teal-50 py-3 rounded-lg font-semibold text-center transition">
                    Message Buyer
                </a>
                @endif
            </div>
        </div>

        {{-- Right: Status & Actions --}}
        <div class="space-y-6">
            {{-- Current Status Card --}}
            <div class="bg-white rounded-lg border border-stone-200 p-6 sticky top-28">
                <h3 class="text-lg font-semibold text-stone-800 mb-4">Status</h3>
                @php
                    $statusColors = [
                        'new' => 'bg-blue-50 text-blue-700 border-blue-200',
                        'contacted' => 'bg-green-50 text-green-700 border-green-200',
                        'site_visit_scheduled' => 'bg-purple-50 text-purple-700 border-purple-200',
                        'negotiating' => 'bg-orange-50 text-orange-700 border-orange-200',
                        'closed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                        'lost' => 'bg-red-50 text-red-700 border-red-200',
                    ];
                    $statusMessages = [
                        'new' => 'Fresh inquiry - ready for your response',
                        'contacted' => 'You have contacted the buyer',
                        'site_visit_scheduled' => 'Site visit has been scheduled',
                        'negotiating' => 'Currently in negotiations',
                        'closed' => 'Deal successfully closed',
                        'lost' => 'Inquiry did not progress',
                    ];
                @endphp
                <div class="border-l-4 rounded-r-lg p-3 {{ $statusColors[$inquiry->status] ?? 'bg-stone-50 text-stone-700 border-stone-200' }}">
                    <p class="font-semibold text-sm">{{ ucfirst(str_replace('_', ' ', $inquiry->status)) }}</p>
                    <p class="text-xs mt-1">{{ $statusMessages[$inquiry->status] ?? 'Inquiry in progress' }}</p>
                </div>

                {{-- Timeline --}}
                <div class="mt-6 space-y-3">
                    <p class="text-xs font-semibold text-stone-500 uppercase">Timeline</p>
                    <div class="space-y-2 text-xs">
                        <div class="flex gap-2">
                            <span class="text-stone-400">{{ $inquiry->created_at->format('M d, Y') }}</span>
                            <span class="text-stone-600">Inquiry received</span>
                        </div>
                        @if($inquiry->updated_at !== $inquiry->created_at)
                        <div class="flex gap-2">
                            <span class="text-stone-400">{{ $inquiry->updated_at->format('M d, Y') }}</span>
                            <span class="text-stone-600">Status updated</span>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Next Steps --}}
            <div class="bg-teal-50 rounded-lg border border-teal-200 p-4">
                <p class="text-xs font-semibold text-teal-900 uppercase mb-3">💡 Suggested Next Steps</p>
                <ul class="text-xs text-teal-900 space-y-2">
                    <li class="flex gap-2">
                        <span class="font-bold">1.</span>
                        <span>Contact the buyer to confirm interest</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="font-bold">2.</span>
                        <span>Provide additional property details</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="font-bold">3.</span>
                        <span>Schedule a site visit if interested</span>
                    </li>
                    <li class="flex gap-2">
                        <span class="font-bold">4.</span>
                        <span>Update inquiry status as progress</span>
                    </li>
                </ul>
            </div>

            {{-- Contact Options --}}
            <div class="bg-white rounded-lg border border-stone-200 p-4 space-y-2">
                <p class="text-xs font-semibold text-stone-500 uppercase mb-3">Contact Options</p>
                <a href="mailto:{{ $inquiry->email }}" class="flex items-center gap-2 p-2 hover:bg-stone-50 rounded transition text-sm">
                    <svg class="w-4 h-4 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    <span>Send Email</span>
                </a>
                <a href="tel:{{ $inquiry->phone }}" class="flex items-center gap-2 p-2 hover:bg-stone-50 rounded transition text-sm">
                    <svg class="w-4 h-4 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                    <span>Call Buyer</span>
                </a>
                @if($inquiry->user)
                <a href="{{ route('agent.messages.index', ['contact' => $inquiry->user->id]) }}" class="flex items-center gap-2 p-2 hover:bg-stone-50 rounded transition text-sm">
                    <svg class="w-4 h-4 text-teal-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    <span>Message Buyer</span>
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
