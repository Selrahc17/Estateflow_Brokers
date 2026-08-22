@extends('layouts.app')
@section('title', 'Inquiries Received')

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-stone-800">Inquiries Received</h1>
            <p class="text-stone-400 mt-1">Manage buyer inquiries for your properties</p>
        </div>
    </div>

    {{-- Filter Tabs --}}
    <div class="flex gap-2 mb-6 overflow-x-auto pb-2">
        @php
            $allCount = \App\Models\Inquiry::where('broker_id', auth()->id())->count();
        @endphp
        <a href="{{ route('broker.inquiries.index') }}" 
           class="px-4 py-2 rounded-lg font-medium whitespace-nowrap transition {{ !request('status') ? 'bg-amber-600 text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}">
            All ({{ $allCount }})
        </a>
        @foreach(['new' => 'New', 'contacted' => 'Contacted', 'site_visit_scheduled' => 'Site Visit Scheduled', 'negotiating' => 'Negotiating', 'closed' => 'Closed', 'lost' => 'Lost'] as $value => $label)
            @php
                $count = \App\Models\Inquiry::where('broker_id', auth()->id())->where('status', $value)->count();
            @endphp
            <a href="{{ route('broker.inquiries.index', ['status' => $value]) }}"
               class="px-4 py-2 rounded-lg font-medium whitespace-nowrap transition {{ request('status') === $value ? 'bg-amber-600 text-white' : 'bg-stone-100 text-stone-700 hover:bg-stone-200' }}">
                {{ $label }} ({{ $count }})
            </a>
        @endforeach
    </div>

    @if($inquiries->count() > 0)
        <div class="space-y-3">
            @foreach($inquiries as $inquiry)
                <div class="bg-white rounded-lg border border-stone-200 p-4 hover:shadow-md transition">
                    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-center">
                        <div>
                            <p class="text-xs text-stone-400 font-semibold uppercase mb-1">Buyer</p>
                            <p class="font-semibold text-stone-800">{{ $inquiry->user?->name ?? 'Guest' }}</p>
                            <p class="text-xs text-stone-400 mt-1">{{ $inquiry->email }}</p>
                        </div>

                        <div>
                            <p class="text-xs text-stone-400 font-semibold uppercase mb-1">Property</p>
                            <a href="{{ route('broker.properties.show', $inquiry->property->id) }}" class="text-amber-600 hover:text-amber-700 font-medium text-sm">
                                {{ $inquiry->property->name }}
                            </a>
                        </div>

                        <div>
                            <p class="text-xs text-stone-400 font-semibold uppercase mb-1">Contact</p>
                            <p class="text-sm font-medium text-stone-700">{{ $inquiry->phone }}</p>
                        </div>

                        <div>
                            <p class="text-xs text-stone-400 font-semibold uppercase mb-1">Status</p>
                            <form action="{{ route('broker.inquiries.status', $inquiry->id) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <select name="status" onchange="this.form.submit()" 
                                        class="text-xs px-2 py-1 rounded border border-stone-200 focus:outline-none focus:ring-2 focus:ring-amber-400">
                                    @foreach($statuses as $status)
                                        <option value="{{ $status }}" {{ $inquiry->status === $status ? 'selected' : '' }}>
                                            {{ ucfirst(str_replace('_', ' ', $status)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                        </div>

                        <div class="text-right">
                            <p class="text-xs text-stone-400 mb-2">{{ $inquiry->created_at->diffForHumans() }}</p>
                            <a href="{{ route('broker.inquiries.show', $inquiry->id) }}" class="text-amber-600 hover:text-amber-700 text-sm font-medium">
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
        <div class="text-center py-12 bg-white rounded-lg border border-stone-200">
            <div class="text-5xl mb-3">📭</div>
            <h3 class="text-xl font-semibold text-stone-700 mb-2">No inquiries yet</h3>
            <p class="text-stone-400">When buyers submit inquiries, they'll appear here</p>
        </div>
    @endif
</div>
@endsection
