@extends('layouts.public')
@section('title', 'All Properties')

@section('content')

<div class="max-w-7xl mx-auto px-4 sm:px-6 py-8">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-stone-800 dark:text-white">
                @if(request('search'))
                    Results for "{{ request('search') }}"
                @elseif(request('type'))
                    {{ request('type') }} Properties
                @elseif(request('status'))
                    {{ request('status') }} Properties
                @else
                    All Properties
                @endif
            </h1>
            <p class="text-stone-400 text-sm mt-1">Browse available properties from our trusted brokers and agents</p>
        </div>
        {{-- Map Toggle --}}
        <div x-data="{ showMap: false }">
            <button @click="showMap = !showMap"
                class="flex items-center gap-2 border border-stone-200 dark:border-stone-700 px-4 py-2 rounded-xl text-sm font-medium text-stone-600 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800 transition">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                <span x-text="showMap ? 'Hide Map' : 'Show Map'"></span>
            </button>

            {{-- Map Panel --}}
            <div x-show="showMap" x-transition class="mt-4">
                <div class="bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-700 overflow-hidden">
                    <div class="px-5 py-3 border-b border-stone-100 dark:border-stone-800 flex items-center justify-between">
                        <p class="font-semibold text-stone-800 dark:text-white text-sm">Property Locations</p>
                        <p class="text-xs text-stone-400">Click a pin to view property details</p>
                    </div>
                    <div id="listings-map" style="height: 400px;"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Pre-Selling Section --}}
    @if(!request('type') && !request('status') && !request('search'))

    <section class="mb-12">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <h2 class="text-lg font-bold text-stone-800">Pre-Selling Properties</h2>
            </div>
            <a href="{{ route('client.properties') }}?status=Pre-Selling" class="text-sm text-amber-600 hover:underline font-medium">View all →</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach([
                ['Palm Residences','Quezon City, Metro Manila','House and Lot','Pre-Selling','₱1,200,000','120 sqm','3','2','https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=400&h=250&fit=crop'],
                ['Greenfield Villas','Laguna','House and Lot','Pre-Selling','₱980,000','110 sqm','2','1','https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=400&h=250&fit=crop'],
                ['Sunrise Homes','Cavite','Lot Only','Pre-Selling','₱450,000','150 sqm','—','—','https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&h=250&fit=crop'],
                ['Hillside Estates','Rizal','House and Lot','Pre-Selling','₱1,350,000','135 sqm','3','2','https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=400&h=250&fit=crop'],
            ] as $p)
            @include('pages.client.properties._card', ['p' => $p])
            @endforeach
        </div>
    </section>

    {{-- RFO Section --}}
    <section class="mb-12">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h2 class="text-lg font-bold text-stone-800">Ready for Occupancy</h2>
            </div>
            <a href="{{ route('client.properties') }}?status=RFO" class="text-sm text-amber-600 hover:underline font-medium">View all →</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach([
                ['Metro Gardens','Bulacan','House and Lot','RFO','₱2,100,000','180 sqm','4','3','https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=400&h=250&fit=crop'],
                ['Coastal View','Batangas','Lot Only','RFO','₱650,000','200 sqm','—','—','https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=400&h=250&fit=crop'],
                ['Villa Serena','Laguna','House and Lot','RFO','₱3,500,000','220 sqm','4','3','https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?w=400&h=250&fit=crop'],
                ['Brentwood Heights','Cavite','House and Lot','RFO','₱1,800,000','160 sqm','3','2','https://images.unsplash.com/photo-1583608205776-bfd35f0d9f83?w=400&h=250&fit=crop'],
            ] as $p)
            @include('pages.client.properties._card', ['p' => $p])
            @endforeach
        </div>
    </section>

    {{-- Lot Only Section --}}
    <section class="mb-12">
        <div class="flex items-center justify-between mb-5">
            <div class="flex items-center gap-2">
                <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                </div>
                <h2 class="text-lg font-bold text-stone-800">Lot Only</h2>
            </div>
            <a href="{{ route('client.properties') }}?type=Lot+Only" class="text-sm text-amber-600 hover:underline font-medium">View all →</a>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
            @foreach([
                ['Sunrise Homes','Cavite','Lot Only','Pre-Selling','₱450,000','150 sqm','—','—','https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&h=250&fit=crop'],
                ['Coastal View','Batangas','Lot Only','RFO','₱650,000','200 sqm','—','—','https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=400&h=250&fit=crop'],
                ['Hillside Lots','Rizal','Lot Only','Pre-Selling','₱380,000','120 sqm','—','—','https://images.unsplash.com/photo-1500382017468-9049fed747ef?w=400&h=250&fit=crop'],
                ['Green Valley','Laguna','Lot Only','Pre-Selling','₱520,000','175 sqm','—','—','https://images.unsplash.com/photo-1501854140801-50d01698950b?w=400&h=250&fit=crop'],
            ] as $p)
            @include('pages.client.properties._card', ['p' => $p])
            @endforeach
        </div>
    </section>

    @else

    {{-- Filtered Results Grid --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
        @foreach([
            ['Palm Residences','Quezon City, Metro Manila','House and Lot','Pre-Selling','₱1,200,000','120 sqm','3','2','https://images.unsplash.com/photo-1564013799919-ab600027ffc6?w=400&h=250&fit=crop'],
            ['Greenfield Villas','Laguna','House and Lot','Pre-Selling','₱980,000','110 sqm','2','1','https://images.unsplash.com/photo-1570129477492-45c003edd2be?w=400&h=250&fit=crop'],
            ['Sunrise Homes','Cavite','Lot Only','Pre-Selling','₱450,000','150 sqm','—','—','https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=400&h=250&fit=crop'],
            ['Hillside Estates','Rizal','House and Lot','Pre-Selling','₱1,350,000','135 sqm','3','2','https://images.unsplash.com/photo-1600596542815-ffad4c1539a9?w=400&h=250&fit=crop'],
            ['Metro Gardens','Bulacan','House and Lot','RFO','₱2,100,000','180 sqm','4','3','https://images.unsplash.com/photo-1600585154340-be6161a56a0c?w=400&h=250&fit=crop'],
            ['Coastal View','Batangas','Lot Only','RFO','₱650,000','200 sqm','—','—','https://images.unsplash.com/photo-1512917774080-9991f1c4c750?w=400&h=250&fit=crop'],
            ['Villa Serena','Laguna','House and Lot','RFO','₱3,500,000','220 sqm','4','3','https://images.unsplash.com/photo-1600047509807-ba8f99d2cdde?w=400&h=250&fit=crop'],
            ['Brentwood Heights','Cavite','House and Lot','RFO','₱1,800,000','160 sqm','3','2','https://images.unsplash.com/photo-1583608205776-bfd35f0d9f83?w=400&h=250&fit=crop'],
        ] as $p)
        @include('pages.client.properties._card', ['p' => $p])
        @endforeach
    </div>

    @endif

</div>

@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var mapEl = document.getElementById('listings-map');
    if (!mapEl) return;

    var map = L.map('listings-map', { center: [14.5, 121.0], zoom: 9, scrollWheelZoom: false });

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
        maxZoom: 19
    }).addTo(map);

    var properties = [
        { lat: 14.6760, lng: 121.0437, name: 'Palm Residences',    location: 'Quezon City',  price: '₱1,200,000', status: 'Pre-Selling', slug: 'palm-residences' },
        { lat: 14.1407, lng: 121.4680, name: 'Greenfield Villas',  location: 'Laguna',       price: '₱980,000',   status: 'Pre-Selling', slug: 'greenfield-villas' },
        { lat: 14.2456, lng: 120.8787, name: 'Sunrise Homes',      location: 'Cavite',       price: '₱450,000',   status: 'Pre-Selling', slug: 'sunrise-homes' },
        { lat: 14.6042, lng: 121.1800, name: 'Hillside Estates',   location: 'Rizal',        price: '₱1,350,000', status: 'Pre-Selling', slug: 'hillside-estates' },
        { lat: 14.7942, lng: 120.8800, name: 'Metro Gardens',      location: 'Bulacan',      price: '₱2,100,000', status: 'RFO',         slug: 'metro-gardens' },
        { lat: 13.7565, lng: 121.0583, name: 'Coastal View',       location: 'Batangas',     price: '₱650,000',   status: 'RFO',         slug: 'coastal-view' },
        { lat: 14.1200, lng: 121.4500, name: 'Villa Serena',       location: 'Laguna',       price: '₱3,500,000', status: 'RFO',         slug: 'villa-serena' },
        { lat: 14.2800, lng: 120.8600, name: 'Brentwood Heights',  location: 'Cavite',       price: '₱1,800,000', status: 'RFO',         slug: 'brentwood-heights' },
    ];

    properties.forEach(function(p) {
        var color = p.status === 'RFO' ? '#16a34a' : '#d97706';
        var icon = L.divIcon({
            className: '',
            html: `<div style="
                background:${color};
                color:white;
                font-size:10px;
                font-weight:700;
                padding:4px 8px;
                border-radius:999px;
                white-space:nowrap;
                box-shadow:0 2px 8px rgba(0,0,0,0.25);
                border:2px solid white;
            ">${p.name}</div>`,
            iconAnchor: [0, 0]
        });

        L.marker([p.lat, p.lng], { icon: icon })
            .addTo(map)
            .bindPopup(`
                <div style="font-family:Inter,sans-serif;min-width:160px;padding:4px">
                    <p style="font-weight:700;font-size:13px;color:#1c1917;margin:0 0 2px">${p.name}</p>
                    <p style="font-size:11px;color:#78716c;margin:0 0 6px">${p.location}</p>
                    <p style="font-size:13px;font-weight:700;color:#d97706;margin:0 0 6px">${p.price}</p>
                    <a href="/estateflow_brokers/public/properties/${p.slug}"
                        style="background:#d97706;color:white;font-size:11px;font-weight:600;padding:4px 10px;border-radius:8px;text-decoration:none;display:inline-block">
                        View Details
                    </a>
                </div>
            `);
    });
});
</script>
@endpush
