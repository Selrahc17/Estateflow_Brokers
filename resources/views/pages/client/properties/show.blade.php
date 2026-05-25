@extends('layouts.public')
@section('title', 'Palm Residences — Property Details')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    {{-- Breadcrumb --}}
    <div class="flex items-center gap-2 text-xs text-stone-400 mb-6">
        <a href="{{ route('client.properties') }}" class="hover:text-amber-600 transition">Properties</a>
        <span>/</span>
        <a href="{{ route('client.properties') }}?type=House+and+Lot" class="hover:text-amber-600 transition">House and Lot</a>
        <span>/</span>
        <span class="text-stone-600">Palm Residences</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">

        {{-- Left: Images + Details --}}
        <div class="lg:col-span-2 space-y-6" x-data="{ activeImg: 0 }">

            {{-- Image Gallery --}}
            <div class="rounded-2xl overflow-hidden">
                <div class="relative h-72 sm:h-96 bg-stone-100">
                    @php $imgs = [
                        'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=800&h=500&fit=crop',
                        'https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=800&h=500&fit=crop',
                        'https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=800&h=500&fit=crop',
                        'https://images.unsplash.com/photo-1583608205776-bfd35f0d9f83?w=800&h=500&fit=crop',
                    ]; @endphp
                    @foreach($imgs as $i => $img)
                    <img src="{{ $img }}" alt="Property Image"
                         x-show="activeImg === {{ $i }}"
                         class="absolute inset-0 w-full h-full object-cover">
                    @endforeach
                    {{-- Badges --}}
                    <div class="absolute top-4 left-4 flex gap-2">
                        <span class="bg-amber-500 text-white text-xs font-semibold px-3 py-1 rounded-full">Pre-Selling</span>
                        <span class="bg-white/90 text-stone-700 text-xs font-medium px-3 py-1 rounded-full">House and Lot</span>
                    </div>
                </div>
                {{-- Thumbnails --}}
                <div class="flex gap-2 mt-2">
                    @foreach($imgs as $i => $img)
                    <button @click="activeImg = {{ $i }}"
                            :class="activeImg === {{ $i }} ? 'ring-2 ring-amber-500' : 'opacity-60 hover:opacity-100'"
                            class="w-20 h-14 rounded-lg overflow-hidden shrink-0 transition">
                        <img src="{{ $img }}" class="w-full h-full object-cover">
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Property Info --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-6">
                <div class="flex items-start justify-between mb-4">
                    <div>
                        <h1 class="text-2xl font-bold text-stone-800">Palm Residences</h1>
                        <p class="text-stone-400 text-sm flex items-center gap-1 mt-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                            Quezon City, Metro Manila, Philippines
                        </p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-stone-400">Starting at</p>
                        <p class="text-2xl font-bold text-amber-600">₱1,200,000</p>
                        <p class="text-xs text-stone-400">₱10,000/sqm</p>
                    </div>
                </div>

                {{-- Specs --}}
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 py-4 border-y border-stone-100 mb-4">
                    @foreach([
                        ['Land Area','120 sqm'],
                        ['Floor Area','85 sqm'],
                        ['Bedrooms','3'],
                        ['Bathrooms','2'],
                    ] as $spec)
                    <div class="text-center">
                        <p class="text-xs text-stone-400 mb-1">{{ $spec[0] }}</p>
                        <p class="font-semibold text-stone-700">{{ $spec[1] }}</p>
                    </div>
                    @endforeach
                </div>

                {{-- Description --}}
                <div>
                    <h3 class="font-semibold text-stone-800 mb-2">About this Property</h3>
                    <p class="text-sm text-stone-500 leading-relaxed">
                        Palm Residences is a premier residential community located in the heart of Quezon City. Offering a blend of modern living and natural surroundings, this development features spacious lots and house-and-lot packages designed for families seeking comfort and convenience. With easy access to major roads, schools, hospitals, and commercial centers, Palm Residences is the ideal place to call home.
                    </p>
                </div>
            </div>

            {{-- Features & Amenities --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-6">
                <h3 class="font-semibold text-stone-800 mb-4">Features & Amenities</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
                    @foreach([
                        ['Interior Features', ['Modern kitchen fixtures','Ceramic tile flooring','Built-in cabinets','Air-conditioning ready','Large windows']],
                        ['Community Features', ['Swimming pool','Basketball court','Clubhouse','Parks & playground','24/7 security']],
                        ['Utilities', ['Water supply','Electricity','Internet ready','Underground drainage','Backup power']],
                    ] as $feat)
                    <div>
                        <p class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-2">{{ $feat[0] }}</p>
                        <ul class="space-y-1.5">
                            @foreach($feat[1] as $item)
                            <li class="flex items-center gap-2 text-sm text-stone-600">
                                <div class="w-1.5 h-1.5 bg-amber-500 rounded-full shrink-0"></div>
                                {{ $item }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Location Map --}}
            <div class="bg-white rounded-2xl border border-stone-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-stone-100 flex items-center justify-between">
                    <h3 class="font-semibold text-stone-800">Location & Map</h3>
                    <a href="https://www.google.com/maps?q=14.6760,121.0437" target="_blank"
                        class="text-xs text-amber-600 hover:underline flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        Open in Google Maps
                    </a>
                </div>

                {{-- Address Info --}}
                <div class="px-6 py-3 bg-stone-50 border-b border-stone-100 flex flex-wrap gap-4 text-sm">
                    <span class="flex items-center gap-1.5 text-stone-600">
                        <svg class="w-4 h-4 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Quezon City, Metro Manila, Philippines
                    </span>
                    <span class="flex items-center gap-1.5 text-stone-500 text-xs">
                        <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                        Coordinates: 14.6760° N, 121.0437° E
                    </span>
                </div>

                {{-- Leaflet Map --}}
                <div id="property-map" style="height: 380px; width: 100%;"></div>

                {{-- Nearby Places --}}
                <div class="px-6 py-4">
                    <p class="text-xs font-semibold text-stone-500 uppercase tracking-widest mb-3">Nearby Places</p>
                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                        @foreach([
                            ['🏫','Schools','SM City QC — 1.2 km'],
                            ['🏥','Hospital','St. Luke\'s Medical — 2.5 km'],
                            ['🛒','Mall','Trinoma Mall — 1.8 km'],
                            ['🚌','Transport','MRT Quezon Ave — 0.9 km'],
                        ] as $place)
                        <div class="bg-stone-50 rounded-xl p-3 text-center">
                            <p class="text-xl mb-1">{{ $place[0] }}</p>
                            <p class="text-xs font-semibold text-stone-700">{{ $place[1] }}</p>
                            <p class="text-xs text-stone-400 mt-0.5">{{ $place[2] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Lot Availability Map --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-stone-800">Lot Availability</h3>
                    <div class="flex items-center gap-3 text-xs">
                        <span class="flex items-center gap-1"><span class="w-3 h-3 bg-green-400 rounded inline-block"></span> Available</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 bg-amber-400 rounded inline-block"></span> Reserved</span>
                        <span class="flex items-center gap-1"><span class="w-3 h-3 bg-red-400 rounded inline-block"></span> Sold</span>
                    </div>
                </div>
                @php
                $lots = [
                    ['1-A','available'],['1-B','reserved'],['1-C','available'],['1-D','sold'],['1-E','available'],['1-F','reserved'],
                    ['2-A','sold'],['2-B','available'],['2-C','reserved'],['2-D','available'],['2-E','sold'],['2-F','reserved'],
                    ['3-A','reserved'],['3-B','available'],['3-C','sold'],['3-D','reserved'],['3-E','available'],['3-F','reserved'],
                    ['4-A','available'],['4-B','reserved'],['4-C','available'],['4-D','sold'],['4-E','reserved'],['4-F','available'],
                ];
                $colors = ['available'=>'bg-green-400 hover:bg-green-500 cursor-pointer','reserved'=>'bg-amber-400','sold'=>'bg-red-400 opacity-60'];
                @endphp
                <div class="grid grid-cols-6 sm:grid-cols-8 gap-2">
                    @foreach($lots as $lot)
                    <div class="relative group">
                        <div class="w-full aspect-square {{ $colors[$lot[1]] }} rounded-lg flex items-center justify-center text-white text-xs font-bold transition">
                            {{ $lot[0] }}
                        </div>
                        @if($lot[1] === 'available')
                        <div class="absolute bottom-full left-1/2 -translate-x-1/2 mb-1 bg-stone-800 text-white text-xs rounded px-2 py-1 whitespace-nowrap opacity-0 group-hover:opacity-100 transition z-10 pointer-events-none">
                            Lot {{ $lot[0] }} — Available
                        </div>
                        @endif
                    </div>
                    @endforeach
                </div>
                <p class="text-xs text-stone-400 mt-3">Click on an available lot to inquire or reserve</p>
            </div>

        </div>

        {{-- Right: Inquiry + Broker --}}
        <div class="space-y-5">

            {{-- Inquiry / Reserve CTA --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-5 sticky top-28">
                <h3 class="font-semibold text-stone-800 mb-1">Interested in this property?</h3>
                <p class="text-xs text-stone-400 mb-4">Send an inquiry or reserve a lot today.</p>

                <div class="space-y-3">
                    <div>
                        <label class="text-xs text-stone-500 mb-1 block">Full Name</label>
                        <input type="text" placeholder="Juan dela Cruz" class="w-full border border-stone-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="text-xs text-stone-500 mb-1 block">Email</label>
                        <input type="email" placeholder="juan@email.com" class="w-full border border-stone-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="text-xs text-stone-500 mb-1 block">Phone</label>
                        <input type="text" placeholder="+63 912 345 6789" class="w-full border border-stone-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="text-xs text-stone-500 mb-1 block">Message</label>
                        <textarea rows="3" placeholder="I'm interested in this property..." class="w-full border border-stone-200 rounded-xl px-3 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 resize-none"></textarea>
                    </div>
                    <a href="{{ route('client.inquiry.success') }}" class="block w-full text-center bg-amber-600 hover:bg-amber-700 text-white py-3 rounded-xl text-sm font-semibold transition">
                        Send Inquiry
                    </a>
                    <a href="{{ route('auth.login') }}" class="block w-full text-center border border-amber-600 text-amber-600 hover:bg-amber-50 py-3 rounded-xl text-sm font-semibold transition">
                        Reserve a Lot
                    </a>
                </div>
            </div>

            {{-- Broker Info --}}
            <div class="bg-white rounded-2xl border border-stone-200 p-5">
                <h3 class="font-semibold text-stone-800 mb-3">Listed by</h3>
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-11 h-11 bg-amber-600 rounded-full flex items-center justify-center text-white font-bold">B</div>
                    <div>
                        <p class="font-medium text-stone-800 text-sm">Broker Name</p>
                        <p class="text-xs text-stone-400">Licensed Real Estate Broker</p>
                        <p class="text-xs text-stone-400">PRC-2024-00123</p>
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
        // Property coordinates — Quezon City
        var lat = 14.6760;
        var lng = 121.0437;

        var map = L.map('property-map', {
            center: [lat, lng],
            zoom: 15,
            scrollWheelZoom: false
        });

        // OpenStreetMap tile layer (free, no API key needed)
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19
        }).addTo(map);

        // Custom amber marker icon
        var icon = L.divIcon({
            className: '',
            html: `<div style="
                width: 36px; height: 36px;
                background: #d97706;
                border: 3px solid white;
                border-radius: 50% 50% 50% 0;
                transform: rotate(-45deg);
                box-shadow: 0 4px 12px rgba(0,0,0,0.3);
            "></div>`,
            iconSize: [36, 36],
            iconAnchor: [18, 36],
            popupAnchor: [0, -40]
        });

        // Add marker
        var marker = L.marker([lat, lng], { icon: icon }).addTo(map);

        marker.bindPopup(`
            <div style="font-family: Inter, sans-serif; min-width: 180px; padding: 4px;">
                <p style="font-weight: 700; font-size: 14px; color: #1c1917; margin: 0 0 4px;">Palm Residences</p>
                <p style="font-size: 12px; color: #78716c; margin: 0 0 6px;">Quezon City, Metro Manila</p>
                <span style="background: #fef3c7; color: #d97706; font-size: 11px; font-weight: 600; padding: 2px 8px; border-radius: 999px;">Pre-Selling</span>
            </div>
        `).openPopup();

        // Nearby markers
        var nearbyPlaces = [
            { lat: 14.6820, lng: 121.0390, name: 'SM City QC', type: 'Mall', icon: '🛒' },
            { lat: 14.6700, lng: 121.0500, name: "St. Luke's Medical", type: 'Hospital', icon: '🏥' },
            { lat: 14.6810, lng: 121.0450, name: 'Trinoma Mall', type: 'Mall', icon: '🛍️' },
            { lat: 14.6740, lng: 121.0380, name: 'MRT Quezon Ave', type: 'Transport', icon: '🚇' },
        ];

        nearbyPlaces.forEach(function(place) {
            var nearbyIcon = L.divIcon({
                className: '',
                html: `<div style="font-size: 22px; line-height: 1;">${place.icon}</div>`,
                iconSize: [28, 28],
                iconAnchor: [14, 14]
            });
            L.marker([place.lat, place.lng], { icon: nearbyIcon })
                .addTo(map)
                .bindPopup(`<b>${place.name}</b><br><small>${place.type}</small>`);
        });

        // Draw a circle radius around property
        L.circle([lat, lng], {
            color: '#d97706',
            fillColor: '#fef3c7',
            fillOpacity: 0.15,
            weight: 1.5,
            radius: 500
        }).addTo(map);
    });
</script>
@endpush
