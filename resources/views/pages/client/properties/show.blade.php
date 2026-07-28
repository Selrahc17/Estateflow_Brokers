@extends('layouts.public')
@section('title', $property->name . ' — Property Details')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-stone-400 mb-6">
        <a href="{{ route('client.properties') }}" class="hover:text-amber-600 transition">Properties</a>
        <span>/</span>
        <span class="text-stone-600">{{ $property->name }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Left: Images + Details --}}
        <div class="lg:col-span-2 space-y-6" x-data="{ activeImg: 0 }">

            {{-- Image Gallery --}}
            <div class="rounded-2xl overflow-hidden">
                <div class="relative h-72 sm:h-96 bg-stone-100">
                    @php
                        $imgs = [];
                        if ($property->featured_image) { $imgs[] = $property->featured_image; }
                        if (!empty($property->images)) {
                            foreach ($property->images as $img) { $imgs[] = $img; }
                        }
                        if (empty($imgs)) {
                            $imgs = ['https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800&h=500&fit=crop'];
                        }
                    @endphp
                    @foreach($imgs as $i => $img)
                    <img src="{{ $img }}" alt="{{ $property->name }}"
                         x-show="activeImg === {{ $i }}"
                         class="absolute inset-0 w-full h-full object-cover">
                    @endforeach
                    {{-- Badges --}}
                    <div class="absolute top-4 left-4 flex gap-2">
                        <span class="{{ $property->status === 'sold' ? 'bg-red-500' : ($property->status === 'coming_soon' ? 'bg-blue-500' : 'bg-amber-500') }} text-white text-xs font-semibold px-3 py-1 rounded-full">
                            {{ ucfirst(str_replace('_', ' ', $property->status)) }}
                        </span>
                        <span class="bg-white/90 text-stone-700 text-xs font-medium px-3 py-1 rounded-full">{{ $property->type }}</span>
                    </div>
                </div>
                {{-- Thumbnails --}}
                @if(count($imgs) > 1)
                <div class="flex gap-2 mt-2 overflow-x-auto pb-1">
                    @foreach($imgs as $i => $img)
                    <button @click="activeImg = {{ $i }}"
                            :class="activeImg === {{ $i }} ? 'ring-2 ring-amber-500' : 'opacity-60 hover:opacity-100'"
                            class="w-20 h-14 rounded-lg overflow-hidden shrink-0 transition">
                        <img src="{{ $img }}" class="w-full h-full object-cover">
                    </button>
                    @endforeach
                </div>
                @endif
            </div>

            {{-- Property Info --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h1 class="text-2xl font-bold text-stone-800">{{ $property->name }}</h1>
                        <p class="text-stone-400 text-sm flex items-center gap-1 mt-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            {{ implode(', ', array_filter([$property->city, $property->province])) ?: ($property->address ?? 'Location TBA') }}
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-stone-400">Starting at</p>
                        <p class="text-2xl font-bold text-amber-600">{{ $property->price ? '₱' . number_format($property->price, 0) : 'Price on request' }}</p>
                    </div>
                </div>

                {{-- Description --}}
                <div class="border-t border-stone-100 pt-4">
                    <h3 class="font-semibold text-stone-800 mb-2">About this Property</h3>
                    <p class="text-sm text-stone-500 leading-relaxed">
                        {{ $property->description ?: 'No description available yet.' }}
                    </p>
                </div>
            </div>

            {{-- Location Map --}}
            <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-100 flex items-center justify-between">
                    <h3 class="font-semibold text-stone-800">Location & Map</h3>
                    @if($property->latitude && $property->longitude)
                    <a href="https://www.google.com/maps?q={{ $property->latitude }},{{ $property->longitude }}" target="_blank"
                        class="text-xs text-amber-600 hover:underline flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Open in Google Maps
                    </a>
                    @endif
                </div>

                {{-- Address Info --}}
                <div class="px-6 py-3 bg-stone-50 border-b border-stone-100 flex flex-wrap gap-4 text-sm">
                    <span class="flex items-center gap-1.5 text-stone-600">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ implode(', ', array_filter([$property->address, $property->city, $property->province])) ?: 'Location TBA' }}
                    </span>
                    @if($property->latitude && $property->longitude)
                    <span class="flex items-center gap-1.5 text-stone-500 text-xs">
                        <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 1l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        Coordinates: {{ number_format($property->latitude, 4) }}° N, {{ number_format($property->longitude, 4) }}° E
                    </span>
                    @endif
                </div>

                {{-- Leaflet Map --}}
                @if($property->latitude && $property->longitude)
                <div id="property-map" style="height: 380px; width: 100%;"></div>
                @endif
            </div>

            <div class="bg-white rounded-2xl border border-stone-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-stone-800">More Properties</h3>
                    <a href="{{ route('client.properties') }}" class="text-sm text-amber-600 hover:underline">Browse all</a>
                </div>

                @if($relatedProperties->isNotEmpty())
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    @foreach($relatedProperties as $relatedProperty)
                        @include('pages.client.properties._card', ['property' => $relatedProperty])
                    @endforeach
                </div>
                @else
                <p class="text-sm text-stone-500">No other properties are available right now. Please check back soon.</p>
                @endif
            </div>

            {{-- Lot Availability Map --}}
            @if($property->lots->count())
            <div class="bg-white rounded-2xl border border-stone-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-stone-800">Lot Availability</h3>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="flex items-center gap-1"><span class="w-3 h-3 bg-green-400 rounded inline-block"></span> Available</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 bg-amber-400 rounded inline-block"></span> Reserved</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 bg-red-400 rounded inline-block"></span> Sold</span>
                    </div>
                </div>
                <div class="grid grid-cols-4 sm:grid-cols-6 gap-2">
                    @foreach($property->lots as $lot)
                    <div class="relative group">
                        <div class="w-full aspect-square {{ $lot->status === 'available' ? 'bg-green-400 hover:bg-green-500 cursor-pointer' : ($lot->status === 'reserved' ? 'bg-amber-400' : 'bg-red-400 opacity-60') }} rounded-lg flex items-center justify-center text-white text-xs font-bold transition">
                            {{ $lot->name }}
                        </div>
                        @if($lot->status === 'available')
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 bg-stone-800 text-white text-xs rounded px-2 py-1 whitespace-nowrap opacity-0 group-hover:opacity-100 transition z-10 pointer-events-none">
                            {{ $lot->name }} — Available
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>

        {{-- Right: Inquiry + Broker --}}
        <div class="space-y-5">

            {{-- Inquiry / Reserve CTA --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-5 sticky top-28">
                <h3 class="font-semibold text-stone-800 mb-1">Interested in this property?</h3>
                <p class="text-xs text-stone-400 mb-4">Send an inquiry or reserve a lot today.</p>

                @auth
                <form action="{{ route('client.account.inquiries.store', $property->id) }}" method="POST" class="space-y-3">
                    @csrf
                    <div>
                        <label class="text-xs text-stone-500 mb-1 block">Full Name</label>
                        <input type="text" value="{{ auth()->user()->name }}" disabled class="w-full border border-stone-200 rounded-xl px-3 py-2.5 text-sm bg-stone-50">
                    </div>
                    <div>
                        <label class="text-xs text-stone-500 mb-1 block">Email *</label>
                        <input type="email" name="email" placeholder="juan@email.com" required class="w-full border border-stone-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 @error('email') ring-2 ring-red-400 @enderror">
                        @error('email') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-stone-500 mb-1 block">Phone *</label>
                        <input type="text" name="phone" placeholder="+63 912 345 6789" required class="w-full border border-stone-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 @error('phone') ring-2 ring-red-400 @enderror">
                        @error('phone') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs text-stone-500 mb-1 block">Message *</label>
                        <textarea name="message" rows="3" placeholder="I'm interested in this property..." required class="w-full border border-stone-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none @error('message') ring-2 ring-red-400 @enderror"></textarea>
                        @error('message') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white py-3 rounded-xl text-sm font-semibold transition">
                        Send Inquiry
                    </button>
                    <div class="flex gap-2">
                        @php
                            $isFavorited = auth()->check() && auth()->user()->favorites()->where('property_id', $property->id)->exists();
                        @endphp
                        <form action="{{ route('client.account.favorites.toggle', $property->id) }}" method="POST" class="flex-1">
                            @csrf
                            <button type="submit" class="w-full border {{ $isFavorited ? 'border-amber-600 bg-amber-50' : 'border-stone-200 hover:bg-stone-50' }} {{ $isFavorited ? 'text-amber-600' : 'text-stone-600' }} py-2 rounded-xl text-sm font-medium transition">
                                {{ $isFavorited ? '❤️ Saved' : '🤍 Save' }}
                            </button>
                        </form>
                    </div>
                </form>
                @else
                <div class="space-y-3">
                    <div class="p-3 bg-amber-50 rounded-lg border border-amber-200">
                        <p class="text-xs text-amber-800">Please log in to send an inquiry</p>
                    </div>
                    <a href="{{ route('auth.login') }}" class="block w-full text-center bg-amber-600 hover:bg-amber-700 text-white py-3 rounded-xl text-sm font-semibold transition">
                        Login to Inquire
                    </a>
                    <a href="{{ route('auth.register') }}" class="block w-full text-center border border-amber-600 text-amber-600 hover:bg-amber-50 py-3 rounded-xl text-sm font-semibold transition">
                        Create Account
                    </a>
                </div>
                @endauth
            </div>

            {{-- Broker Info --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-5">
                <h3 class="font-semibold text-stone-800 mb-3">Listed by</h3>
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 bg-amber-600 rounded-full flex items-center justify-center text-white font-bold">{{ substr($property->broker->name, 0, 1) }}</div>
                    <div>
                        <p class="font-medium text-stone-800 text-sm">{{ $property->broker->name }}</p>
                        <p class="text-xs text-stone-400">Licensed Real Estate Broker</p>
                    </div>
                </div>
                <a href="{{ route('client.account.chat') }}" class="block w-full text-center border border-stone-200 text-stone-600 hover:bg-stone-50 py-2 rounded-xl text-sm font-medium transition">
                    Contact Broker
                </a>
            </div>

            {{-- AI Assistant CTA --}}
            <div class="bg-gradient-to-br from-stone-800 to-amber-900 rounded-2xl p-5 text-white">
                <p class="font-semibold mb-1">Have questions?</p>
                <p class="text-stone-300 text-xs mb-3">Our AI Assistant can answer your inquiries instantly.</p>
                <a href="{{ route('client.account.chat') }}" class="block w-full text-center bg-amber-600 hover:bg-amber-700 py-2.5 rounded-xl text-sm font-medium transition">
                    Ask AI Assistant
                </a>
            </div>

        </div>
    </div>

</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        @if($property->latitude && $property->longitude)
        var lat = {{ $property->latitude }};
        var lng = {{ $property->longitude }};

        var map = L.map('property-map', { center: [lat, lng], zoom: 15, scrollWheelZoom: false });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

        var icon = L.divIcon({
            className: '',
            html: `<div style="width:36px;height:36px;background:#d97706;border:3px solid white;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 4px 12px rgba(0,0,0,0.3);"></div>`,
            iconSize: [36, 36], iconAnchor: [18, 36], popupAnchor: [0, -40]
        });

        var marker = L.marker([lat, lng], { icon: icon }).addTo(map);
        marker.bindPopup(`
            <div style="font-family:Inter,sans-serif;min-width:180px;padding:4px;">
                <p style="font-weight:700;font-size:14px;color:#1c1917;margin:0 0 4px;">{{ $property->name }}</p>
                <p style="font-size:12px;color:#78716c;margin:0 0 6px;">{{ implode(', ', array_filter([$property->city, $property->province])) ?: ($property->address ?? '') }}</p>
            </div>
        `).openPopup();

        L.circle([lat, lng], { color: '#d97706', fillColor: '#fef3c7', fillOpacity: 0.15, weight: 1.5, radius: 500 }).addTo(map);
        @endif
    });
</script>
@endpush
