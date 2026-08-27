@extends('layouts.app')
@section('title', 'Edit Property')
@section('page-title', 'Edit Property')
@section('page-subtitle', $property->name)

@section('content')
<div class="max-w-3xl mx-auto bg-white rounded-xl border border-stone-200 p-6"
     x-data="{ ...mapPicker({{ $property->latitude ?? 'null' }}, {{ $property->longitude ?? 'null' }}), selectedType: '{{ old('type', $property->type ?? '') }}' }">
    <form method="POST" action="{{ route('agent.properties.update', $property) }}" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Property Name</label>
                <input type="text" name="name" value="{{ old('name', $property->name) }}" required
                       class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
            </div>

            <div>
                <div class="flex items-center justify-between mb-1">
                    <label class="block text-sm font-medium text-stone-700">Description</label>
                    <button type="button" data-ai-describe class="text-xs text-amber-700 hover:underline">Generate with AI</button>
                </div>
                <textarea id="property-description" name="description" rows="3" class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">{{ old('description', $property->description) }}</textarea>
            </div>

            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Property Type</label>
                    <select name="type" x-model="selectedType" class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400 @error('type') border-red-400 @enderror">
                        @foreach(['House and Lot','Condominium','Townhouse','Lot Only','Office Space','Warehouse','Farm','Villa','Apartment'] as $type)
                        <option value="{{ $type }}" {{ old('type', $property->type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                        @endforeach
                    </select>
                    @error('type') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Price (₱)</label>
                    <input type="number" name="price" step="0.01" value="{{ old('price', $property->price) }}"
                           class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Status</label>
                    <select name="status" class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        @foreach(['available' => 'Available', 'coming_soon' => 'Coming Soon', 'sold' => 'Sold'] as $val => $label)
                        <option value="{{ $val }}" {{ old('status', $property->status) === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="border border-stone-200 rounded-xl p-4 bg-stone-50">
                <p class="text-sm font-semibold text-stone-700 mb-3">Property Specifications</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-stone-500 mb-1">Bedrooms</label>
                        <input type="number" name="bedrooms" min="0" value="{{ old('bedrooms', $property->bedrooms) }}" placeholder="—" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-stone-500 mb-1">Bathrooms</label>
                        <input type="number" name="bathrooms" min="0" value="{{ old('bathrooms', $property->bathrooms) }}" placeholder="—" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-stone-500 mb-1">Stories / Floors</label>
                        <input type="number" name="stories" min="0" value="{{ old('stories', $property->stories) }}" placeholder="—" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-stone-500 mb-1">Floor Area (sqm)</label>
                        <input type="number" step="0.01" name="floor_area" min="0" value="{{ old('floor_area', $property->floor_area) }}" placeholder="—" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-stone-500 mb-1">Lot Area (sqm)</label>
                        <input type="number" step="0.01" name="lot_area" min="0" value="{{ old('lot_area', $property->lot_area) }}" placeholder="—" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-stone-500 mb-1">Frontage (m)</label>
                        <input type="number" step="0.01" name="frontage" min="0" value="{{ old('frontage', $property->frontage) }}" placeholder="—" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-stone-500 mb-1">Parking Slots</label>
                        <input type="number" name="parking_slots" min="0" value="{{ old('parking_slots', $property->parking_slots) }}" placeholder="—" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    </div>
                </div>
            </div>

            {{-- Location Section --}}
            <div class="border border-stone-200 rounded-xl overflow-hidden">
                <div class="px-4 py-3 bg-stone-50 border-b border-stone-200 flex items-center justify-between">
                    <p class="text-sm font-semibold text-stone-700 flex items-center gap-2">
                        <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Location & Map Pin
                    </p>
                    <button type="button" @click="useMyLocation"
                            class="flex items-center gap-1.5 text-xs text-amber-700 bg-amber-50 hover:bg-amber-100 border border-amber-200 px-3 py-1.5 rounded-lg transition font-medium">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                        Use My Location
                    </button>
                </div>

                <div class="p-4 space-y-3">
                    <div class="relative">
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <input type="text" x-model="searchQuery"
                                       @input.debounce.400ms="liveSearch"
                                       @keydown.enter.prevent="liveSearch"
                                       @keydown.escape="results = []"
                                       placeholder="Type a place name, barangay, city, or province..."
                                       class="w-full border border-stone-200 rounded-lg pl-9 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                                <svg class="w-4 h-4 text-stone-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                <span x-show="searching" class="absolute right-3 top-3">
                                    <svg class="w-4 h-4 text-amber-500 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                                </span>
                            </div>
                            <button type="button" @click="clearPin" x-show="lat !== null"
                                    class="text-xs text-red-500 hover:text-red-700 border border-red-200 hover:bg-red-50 px-3 py-2 rounded-lg transition">
                                Clear Pin
                            </button>
                        </div>

                        <div x-show="results.length > 0" x-transition
                             class="absolute z-50 w-full mt-1 bg-white border border-stone-200 rounded-xl shadow-lg overflow-hidden">
                            <template x-for="r in results" :key="r.place_id">
                                <button type="button" @click="selectResult(r)"
                                        class="w-full text-left px-4 py-3 text-sm hover:bg-amber-50 border-b border-stone-100 last:border-0 transition flex items-start gap-3">
                                    <svg class="w-4 h-4 text-amber-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                    <div>
                                        <p class="font-medium text-stone-800 text-xs" x-text="r.display_name.split(',')[0]"></p>
                                        <p class="text-stone-400 text-xs mt-0.5" x-text="r.display_name.split(',').slice(1,4).join(',')"></p>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>

                    <div class="relative">
                        <div id="property-map" class="w-full rounded-lg border border-stone-200 overflow-hidden" style="height: 360px;"></div>
                        <div x-show="lat === null"
                             class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="bg-white/90 backdrop-blur rounded-xl px-4 py-3 text-center shadow-sm border border-stone-200">
                                <p class="text-sm font-medium text-stone-600">Search a place or click the map to pin</p>
                                <p class="text-xs text-stone-400 mt-0.5">You can also drag the pin after placing it</p>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-medium text-stone-500 mb-1">Full Address <span class="text-stone-400 font-normal">(auto-filled, editable)</span></label>
                            <input type="text" name="address" x-model="address"
                                   placeholder="Will be filled when you pin a location"
                                   class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-stone-500 mb-1">City / Municipality</label>
                            <input type="text" name="city" x-model="city"
                                   class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-stone-500 mb-1">Province</label>
                            <input type="text" name="province" x-model="province"
                                   class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-stone-500 mb-1">Region</label>
                            <input type="text" x-model="region" readonly
                                   class="w-full border border-stone-100 bg-stone-50 rounded-lg px-3 py-2 text-sm text-stone-500 cursor-default">
                        </div>
                    </div>

                    <div class="border-t border-stone-100 pt-3">
                        <p class="text-xs font-medium text-stone-500 mb-2">Manual Coordinates <span class="text-stone-400 font-normal">(or paste from Google Maps)</span></p>
                        <div class="flex gap-2 items-center">
                            <input type="number" step="any" x-model="manualLat" placeholder="Latitude"
                                   class="flex-1 border border-stone-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-amber-400 font-mono">
                            <input type="number" step="any" x-model="manualLng" placeholder="Longitude"
                                   class="flex-1 border border-stone-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-amber-400 font-mono">
                            <button type="button" @click="applyManualCoords"
                                    class="bg-stone-700 hover:bg-stone-800 text-white px-3 py-2 rounded-lg text-xs font-medium transition whitespace-nowrap">
                                Go
                            </button>
                        </div>
                    </div>

                    <input type="hidden" name="latitude" x-model="lat">
                    <input type="hidden" name="longitude" x-model="lng">

                    <div class="flex items-center justify-between text-xs">
                        <span class="text-stone-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" :class="lat ? 'text-green-500' : 'text-stone-300'" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/></svg>
                            <span x-text="lat && lng ? 'Pinned: ' + parseFloat(lat).toFixed(6) + ', ' + parseFloat(lng).toFixed(6) : 'No pin set'"></span>
                        </span>
                        <span x-show="lat" class="text-green-600 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Location saved
                        </span>
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Featured Image</label>
                @if($property->featured_image)
                    <div class="mb-2"><img src="{{ $property->featured_image }}" class="h-24 rounded-lg object-cover"></div>
                @endif
                <input type="file" name="featured_image" accept="image/*"
                       class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
            </div>

            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Additional Images <span class="text-stone-400 font-normal text-xs">(up to 10 photos)</span></label>
                @if(!empty($property->images))
                    <div class="flex flex-wrap gap-2 mb-2">
                        @foreach($property->images as $img)
                            <img src="{{ $img }}" class="h-20 w-28 rounded-lg object-cover border border-stone-200">
                        @endforeach
                    </div>
                @endif
                <input type="file" name="images[]" accept="image/*" multiple
                       class="w-full border border-stone-200 rounded-lg px-4 py-2.5 text-sm file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100">
                <p class="text-xs text-stone-400 mt-1">Uploading new images will replace the existing ones.</p>
            </div>
        </div>

        <div class="flex items-center gap-3 mt-6 pt-6 border-t border-stone-200">
            <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-6 py-2.5 rounded-lg text-sm font-medium transition">Update Property</button>
            <a href="{{ route('agent.properties.index') }}" class="text-stone-500 hover:text-stone-700 text-sm">Cancel</a>
        </div>
    </form>

    @push('scripts')
    <script>
    document.querySelector('[data-ai-describe]')?.addEventListener('click', async (button) => {
        const target = document.getElementById('property-description');
        button.currentTarget.disabled = true;
        button.currentTarget.textContent = 'Generating...';
        const response = await fetch('{{ route('agent.ai.describe') }}', {
            method: 'POST',
            headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json'},
            body: JSON.stringify(Object.fromEntries(new FormData(document.querySelector('form'))))
        });
        const result = await response.json();
        target.value = result.description || 'AI description is unavailable. Add an OpenAI API key or write the description manually.';
        button.currentTarget.disabled = false;
        button.currentTarget.textContent = 'Generate with AI';
    });
    </script>
    @endpush
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
function mapPicker(initLat, initLng) {
    const hasPin = initLat !== null && initLng !== null;
    return {
        lat:        hasPin ? initLat : null,
        lng:        hasPin ? initLng : null,
        address:    '{{ addslashes(old('address', $property->address ?? '')) }}',
        city:       '{{ addslashes(old('city', $property->city ?? '')) }}',
        province:   '{{ addslashes(old('province', $property->province ?? '')) }}',
        region:     '',
        searchQuery: '',
        results:    [],
        searching:  false,
        manualLat:  hasPin ? initLat : '',
        manualLng:  hasPin ? initLng : '',
        map:        null,
        marker:     null,

        init() {
            this.$nextTick(() => {
                const center = hasPin ? [initLat, initLng] : [12.8797, 121.7740];
                const zoom   = hasPin ? 15 : 6;
                this.map = L.map('property-map', { center, zoom, zoomControl: true });
                L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                    attribution: '&copy; <a href="https://openstreetmap.org">OpenStreetMap</a>',
                    maxZoom: 19
                }).addTo(this.map);
                this.map.on('click', (e) => this.placePin(e.latlng.lat, e.latlng.lng, true));
                if (hasPin) this.placePin(initLat, initLng, false);
            });
        },

        placePin(lat, lng, reverseGeocode = false) {
            this.lat = lat;
            this.lng = lng;
            this.manualLat = parseFloat(lat).toFixed(6);
            this.manualLng = parseFloat(lng).toFixed(6);

            const icon = L.divIcon({
                className: '',
                html: `<div style="width:32px;height:32px;background:#d97706;border:3px solid white;border-radius:50% 50% 50% 0;transform:rotate(-45deg);box-shadow:0 2px 8px rgba(0,0,0,0.3)"></div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 32]
            });

            if (this.marker) {
                this.marker.setLatLng([lat, lng]);
            } else {
                this.marker = L.marker([lat, lng], { draggable: true, icon }).addTo(this.map);
                this.marker.on('drag', (e) => {
                    const p = e.target.getLatLng();
                    this.lat = p.lat; this.lng = p.lng;
                    this.manualLat = p.lat.toFixed(6); this.manualLng = p.lng.toFixed(6);
                });
                this.marker.on('dragend', (e) => {
                    const p = e.target.getLatLng();
                    this.placePin(p.lat, p.lng, true);
                });
            }

            this.map.setView([lat, lng], this.map.getZoom() < 14 ? 15 : this.map.getZoom());
            if (reverseGeocode) this.reverseGeocode(lat, lng);
        },

        async liveSearch() {
            const q = this.searchQuery.trim();
            if (q.length < 2) { this.results = []; return; }
            this.searching = true;
            try {
                const res = await fetch(`/api/geocode/search?q=${encodeURIComponent(q)}`);
                this.results = await res.json();
            } finally { this.searching = false; }
        },

        selectResult(r) {
            this.results = [];
            this.searchQuery = r.display_name.split(',')[0].trim();
            this.placePin(parseFloat(r.lat), parseFloat(r.lon), false);
            this.fillAddress(r.address ?? {}, r.display_name);
        },

        async reverseGeocode(lat, lng) {
            try {
                const res = await fetch(`/api/geocode/reverse?lat=${lat}&lon=${lng}`);
                const data = await res.json();
                if (data?.address) this.fillAddress(data.address, data.display_name);
            } catch(e) {}
        },

        fillAddress(addr, displayName) {
            // PH: road/suburb = street, city/town/municipality = city, province = province, state = region
            const streetParts = [
                addr.house_number || '',
                addr.road || addr.pedestrian || addr.footway || addr.path || '',
                addr.suburb || addr.neighbourhood || addr.quarter || addr.village || '',
            ].filter(Boolean);
            this.address = streetParts.length
                ? streetParts.join(' ')
                : displayName.split(',').slice(0, 3).join(',').trim();

            this.city = addr.city
                || addr.city_district
                || addr.town
                || addr.municipality
                || addr.county
                || addr.village
                || '';

            this.province = addr.province || addr.state_district || '';
            this.region   = addr.state || '';

            if (this.marker) {
                this.marker.bindPopup(
                    `<div style="font-family:sans-serif;min-width:160px;padding:2px">
                        <p style="font-weight:700;font-size:13px;margin:0 0 3px;color:#1c1917">${this.city || displayName.split(',')[0]}</p>
                        <p style="font-size:11px;color:#78716c;margin:0">${[this.province, this.region].filter(Boolean).join(', ')}</p>
                    </div>`
                ).openPopup();
            }
        },

        applyManualCoords() {
            const lat = parseFloat(this.manualLat);
            const lng = parseFloat(this.manualLng);
            if (isNaN(lat) || isNaN(lng) || lat < -90 || lat > 90 || lng < -180 || lng > 180) return;
            this.placePin(lat, lng, true);
        },

        clearPin() {
            if (this.marker) { this.map.removeLayer(this.marker); this.marker = null; }
            this.lat = null; this.lng = null;
            this.address = ''; this.city = ''; this.province = ''; this.region = '';
            this.manualLat = ''; this.manualLng = '';
            this.searchQuery = '';
        },

        useMyLocation() {
            if (!navigator.geolocation) return;
            navigator.geolocation.getCurrentPosition(
                (pos) => this.placePin(pos.coords.latitude, pos.coords.longitude, true),
                () => alert('Could not get your location.')
            );
        }
    }
}
</script>
@endsection
