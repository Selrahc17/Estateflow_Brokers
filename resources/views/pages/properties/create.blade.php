@extends('layouts.app')
@section('title', 'Add Property')
@section('page-title', 'Add New Property')
@section('page-subtitle', 'Create a new property listing')

@section('content')
@php
    $field = 'w-full rounded-lg border border-stone-200 bg-white px-3 py-2.5 text-sm text-stone-800 placeholder:text-stone-400 focus:outline-none focus:ring-2 focus:ring-teal-400';
    $fieldError = fn (string $name) => $errors->has($name) ? 'border-red-400' : '';
    $label = 'mb-1 block text-sm font-medium text-stone-700';
    $sectionHeader = 'flex items-start gap-3 border-b border-stone-100 px-5 py-4';
    $sectionNumber = 'flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-teal-100 text-sm font-semibold text-teal-800';
    $oldLatitude = is_numeric(old('latitude')) ? (float) old('latitude') : null;
    $oldLongitude = is_numeric(old('longitude')) ? (float) old('longitude') : null;
@endphp
<div class="mx-auto max-w-3xl" x-data="{ ...mapPicker(@js($oldLatitude), @js($oldLongitude), @js($oldLatitude !== null && $oldLongitude !== null)), selectedType: @js(old('type', '')), lotTypes: @js(\App\Models\Property::LOT_TYPES) }">
    <form method="POST" action="{{ route('agent.properties.store') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        @if($errors->any() && ! session('duplicate_property'))
            <div role="alert" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">Please check the {{ $errors->count() === 1 ? 'field' : 'fields' }} marked below.</div>
        @endif

        @if(session('duplicate_property'))
            <div class="rounded-xl border border-yellow-200 bg-yellow-50 p-4">
                <h4 class="mb-2 flex items-center gap-2 text-sm font-semibold text-yellow-800">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                    Potential Duplicate Found!
                </h4>
                <div class="mb-3 rounded-lg border border-yellow-300 bg-white p-3">
                    <p class="text-sm font-medium text-stone-700">{{ session('duplicate_property')->name }}</p>
                    <p class="mt-1 text-xs text-stone-500">{{ implode(', ', array_filter([session('duplicate_property')->address, session('duplicate_property')->city, session('duplicate_property')->province])) }}</p>
                </div>
                <label class="flex cursor-pointer items-center gap-2 text-sm text-yellow-800">
                    <input type="checkbox" name="confirm_duplicate" class="rounded">
                    I confirm this is not a duplicate and want to proceed
                </label>
            </div>
        @endif

        {{-- Basic details --}}
        <section class="rounded-xl border border-stone-200 bg-white" aria-labelledby="section-details">
            <div class="{{ $sectionHeader }}">
                <span class="{{ $sectionNumber }}" aria-hidden="true">1</span>
                <div>
                    <h2 id="section-details" class="font-semibold text-stone-800">Basic Details</h2>
                    <p class="text-xs text-stone-500">What clients will see first.</p>
                </div>
            </div>
            <div class="space-y-4 p-5">
                <div>
                    <label for="name" class="{{ $label }}">Property Name <span class="text-red-600" aria-hidden="true">*</span></label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Vista Homes Phase 2" class="{{ $field }} {{ $fieldError('name') }}">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>

                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <label for="type" class="{{ $label }}">Property Type <span class="text-red-600" aria-hidden="true">*</span></label>
                        <select id="type" name="type" x-model="selectedType" required class="{{ $field }} {{ $fieldError('type') }}">
                            <option value="">Select property type</option>
                            @foreach(\App\Models\Property::TYPES as $type)
                            <option value="{{ $type }}" @selected(old('type') === $type)>{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="status" class="{{ $label }}">Status <span class="text-red-600" aria-hidden="true">*</span></label>
                        <select id="status" name="status" class="{{ $field }} {{ $fieldError('status') }}">
                            <option value="available" @selected(old('status', 'available') === 'available')>Available</option>
                            <option value="coming_soon" @selected(old('status') === 'coming_soon')>Coming Soon</option>
                            <option value="sold" @selected(old('status') === 'sold')>Sold</option>
                        </select>
                        @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <div class="mb-1 flex items-center justify-between gap-3">
                        <label for="property-description" class="text-sm font-medium text-stone-700">Description</label>
                        <button type="button" data-ai-describe class="inline-flex items-center gap-1.5 rounded-lg border border-teal-200 px-2.5 py-1 text-xs font-medium text-teal-800 transition hover:bg-teal-50 disabled:cursor-wait disabled:opacity-60">Generate with AI</button>
                    </div>
                    <textarea id="property-description" name="description" rows="4" placeholder="Describe the property, its location, and what makes it stand out." class="{{ $field }}">{{ old('description') }}</textarea>
                    <p data-ai-status role="status" class="mt-1 text-xs text-stone-600"></p>
                </div>
            </div>
        </section>

        {{-- Pricing --}}
        <section class="rounded-xl border border-stone-200 bg-white" aria-labelledby="section-pricing">
            <div class="{{ $sectionHeader }}">
                <span class="{{ $sectionNumber }}" aria-hidden="true">2</span>
                <div>
                    <h2 id="section-pricing" class="font-semibold text-stone-800">Pricing and Terms</h2>
                    <p class="text-xs text-stone-500">Leave the price blank to show "Price on request".</p>
                </div>
            </div>
            <div class="grid grid-cols-1 gap-4 p-5 sm:grid-cols-3">
                <div>
                    <label for="price" class="{{ $label }}">Price (₱)</label>
                    <input id="price" type="number" name="price" step="0.01" min="0" value="{{ old('price') }}" placeholder="0.00" class="{{ $field }} {{ $fieldError('price') }}">
                    @error('price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="agent_commission" class="{{ $label }}">Commission Rate (%)</label>
                    <input id="agent_commission" type="number" name="agent_commission" step="0.01" min="0" max="100" value="{{ old('agent_commission') }}" placeholder="0" class="{{ $field }} {{ $fieldError('agent_commission') }}">
                    @error('agent_commission')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="valid_until" class="{{ $label }}">Valid Until</label>
                    <input id="valid_until" type="date" name="valid_until" value="{{ old('valid_until') }}" class="{{ $field }} {{ $fieldError('valid_until') }}">
                    @error('valid_until')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
        </section>

        {{-- Specifications --}}
        <section class="rounded-xl border border-stone-200 bg-white" aria-labelledby="section-specs">
            <div class="{{ $sectionHeader }}">
                <span class="{{ $sectionNumber }}" aria-hidden="true">3</span>
                <div>
                    <h2 id="section-specs" class="font-semibold text-stone-800">Specifications</h2>
                    <p class="text-xs text-stone-500" x-show="!lotTypes.includes(selectedType)">The fields change with the property type.</p>
                    <p class="text-xs text-stone-500" x-show="lotTypes.includes(selectedType)" x-cloak>Use the Lot Availability screen for individual lots under a development.</p>
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 p-5 sm:grid-cols-3">
                @foreach([
                    ['bedrooms', 'Bedrooms', '1', "!lotTypes.includes(selectedType)"],
                    ['bathrooms', 'Bathrooms', '1', "!lotTypes.includes(selectedType)"],
                    ['stories', 'Stories / Floors', '1', "!lotTypes.includes(selectedType)"],
                    ['floor_area', 'Floor Area (sqm)', '0.01', "!lotTypes.includes(selectedType)"],
                    ['parking_slots', 'Parking Slots', '1', "!lotTypes.includes(selectedType)"],
                    ['lot_area', 'Lot Area (sqm)', '0.01', "!['Condominium', 'Apartment', 'Office Space'].includes(selectedType)"],
                    ['frontage', 'Frontage (m)', '0.01', "lotTypes.includes(selectedType)"],
                ] as [$name, $text, $step, $condition])
                <div x-show="{{ $condition }}" @if($name === 'frontage') x-cloak @endif>
                    <label for="{{ $name }}" class="{{ $label }}">{{ $text }}</label>
                    <input id="{{ $name }}" type="number" name="{{ $name }}" step="{{ $step }}" min="0" value="{{ old($name) }}" placeholder="—" class="{{ $field }} {{ $fieldError($name) }}">
                    @error($name)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                @endforeach
            </div>
        </section>

        {{-- Location --}}
        <section class="rounded-xl border border-stone-200 bg-white" aria-labelledby="section-location">
            <div class="{{ $sectionHeader }} justify-between">
                <div class="flex items-start gap-3">
                    <span class="{{ $sectionNumber }}" aria-hidden="true">4</span>
                    <div>
                        <h2 id="section-location" class="font-semibold text-stone-800">Location</h2>
                        <p class="text-xs text-stone-500">Search or click the map to fill in the address.</p>
                    </div>
                </div>
                <button type="button" @click="useMyLocation" class="flex shrink-0 items-center gap-1.5 rounded-lg border border-teal-200 bg-teal-50 px-3 py-1.5 text-xs font-medium text-teal-800 transition hover:bg-teal-100">
                    <svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5c-1.38 0-2.5-1.12-2.5-2.5s1.12-2.5 2.5-2.5 2.5 1.12 2.5 2.5-1.12 2.5-2.5 2.5z"/></svg>
                    Use My Location
                </button>
            </div>
            <div class="space-y-3 p-5">
                    {{-- Live search with debounce --}}
                    <div class="relative">
                        <div class="flex gap-2">
                            <div class="relative flex-1">
                                <input type="text" x-model="searchQuery"
                                       @input.debounce.400ms="liveSearch"
                                       @keydown.enter.prevent="liveSearch"
                                       @keydown.escape="results = []"
                                       placeholder="Type a place name, barangay, city, or province..."
                                       class="w-full border border-stone-200 rounded-lg pl-9 pr-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                                <svg class="w-4 h-4 text-stone-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/></svg>
                                <span x-show="searching" class="absolute right-3 top-3">
                                    <svg class="w-4 h-4 text-teal-600 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                                </span>
                            </div>
                            <button type="button" @click="clearPin"
                                    x-show="lat !== null"
                                    class="text-xs text-red-700 hover:text-red-800 border border-red-200 hover:bg-red-50 px-3 py-2 rounded-lg transition">
                                Clear Pin
                            </button>
                        </div>

                        {{-- Dropdown results --}}
                        <div x-show="results.length > 0" x-transition
                             class="absolute z-50 w-full mt-1 bg-white border border-stone-200 rounded-xl shadow-lg overflow-hidden">
                            <template x-for="r in results" :key="r.place_id">
                                <button type="button" @click="selectResult(r)"
                                        class="w-full text-left px-4 py-3 text-sm hover:bg-teal-50 border-b border-stone-100 last:border-0 transition flex items-start gap-3">
                                    <svg class="w-4 h-4 text-teal-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                    <div>
                                        <p class="font-medium text-stone-800 text-xs" x-text="r.display_name.split(',')[0]"></p>
                                        <p class="text-stone-400 text-xs mt-0.5" x-text="r.display_name.split(',').slice(1,4).join(',')"></p>
                                    </div>
                                </button>
                            </template>
                        </div>
                    </div>

                    {{-- Map --}}
                    <div class="relative">
                        <div id="property-map" class="w-full rounded-lg border border-stone-200 overflow-hidden" style="height: 360px;"></div>
                        {{-- Map hint overlay --}}
                        <div x-show="lat === null"
                             class="absolute inset-0 flex items-center justify-center pointer-events-none">
                            <div class="bg-white/90 backdrop-blur rounded-xl px-4 py-3 text-center shadow-sm border border-stone-200">
                                <p class="text-sm font-medium text-stone-600">Search a place or click the map to pin</p>
                                <p class="text-xs text-stone-400 mt-0.5">You can also drag the pin after placing it</p>
                            </div>
                        </div>
                    </div>

                    {{-- Auto-filled address fields --}}
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <div class="sm:col-span-3">
                            <label class="block text-xs font-medium text-stone-500 mb-1">Full Address <span class="text-stone-400 font-normal">(auto-filled, editable)</span></label>
                            <input type="text" name="address" x-model="address"
                                   placeholder="Will be filled when you pin a location"
                                   class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-stone-500 mb-1">City / Municipality</label>
                            <input type="text" name="city" x-model="city"
                                   placeholder="Auto-filled"
                                   class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-stone-500 mb-1">Province</label>
                            <input type="text" name="province" x-model="province"
                                   :placeholder="lat ? 'Type the province' : 'Auto-filled'"
                                   class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-stone-500 mb-1">Region</label>
                            <input type="text" x-model="region" readonly
                                   placeholder="Auto-filled"
                                   class="w-full border border-stone-100 bg-stone-50 rounded-lg px-3 py-2 text-sm text-stone-500 cursor-default">
                        </div>
                    </div>

                    {{-- Manual coordinate input --}}
                    <div class="border-t border-stone-100 pt-3">
                        <p class="text-xs font-medium text-stone-500 mb-2">Manual Coordinates <span class="text-stone-400 font-normal">(or paste from Google Maps)</span></p>
                        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                            <input type="number" step="any" x-model="manualLat" placeholder="Latitude (e.g. 14.5995)"
                                   class="min-w-0 flex-1 border border-stone-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-teal-400 font-mono">
                            <input type="number" step="any" x-model="manualLng" placeholder="Longitude (e.g. 120.9842)"
                                   class="min-w-0 flex-1 border border-stone-200 rounded-lg px-3 py-2 text-xs focus:outline-none focus:ring-2 focus:ring-teal-400 font-mono">
                            <button type="button" @click="applyManualCoords"
                                    class="bg-stone-700 hover:bg-stone-800 text-white px-3 py-2 rounded-lg text-xs font-medium transition whitespace-nowrap">
                                Go
                            </button>
                        </div>
                    </div>

                    {{-- Hidden inputs + pin status --}}
                    <input type="hidden" name="latitude" x-model="lat">
                    <input type="hidden" name="longitude" x-model="lng">

                    <div class="flex items-center justify-between text-xs">
                        <span class="text-stone-400 flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" :class="lat ? 'text-green-500' : 'text-stone-300'" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/></svg>
                            <span x-text="lat && lng ? 'Pinned: ' + parseFloat(lat).toFixed(6) + ', ' + parseFloat(lng).toFixed(6) : 'No pin set'"></span>
                        </span>
                        <span x-show="lat" class="text-green-700 font-medium flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Location saved
                        </span>
                    </div>
                </div>
        </section>

        {{-- Photos --}}
        <section class="rounded-xl border border-stone-200 bg-white" aria-labelledby="section-photos">
            <div class="{{ $sectionHeader }}">
                <span class="{{ $sectionNumber }}" aria-hidden="true">5</span>
                <div>
                    <h2 id="section-photos" class="font-semibold text-stone-800">Photos</h2>
                    <p class="text-xs text-stone-500">The featured image is the one clients see on the listing card.</p>
                </div>
            </div>
            <div class="space-y-4 p-5">
                @include('pages.properties._image-fields')
            </div>
        </section>

        {{-- Actions stay in reach on a long form --}}
        <div class="sticky bottom-3 z-30 flex items-center justify-between gap-3 rounded-xl border border-stone-200 bg-white/95 px-5 py-3 shadow-lg backdrop-blur">
            <p class="hidden text-xs text-stone-500 sm:block"><span class="text-red-600" aria-hidden="true">*</span> Required fields</p>
            <div class="flex flex-1 items-center justify-end gap-3 sm:flex-none">
                <a href="{{ route('agent.properties.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-stone-600 transition hover:bg-stone-50">Cancel</a>
                <button type="submit" class="rounded-lg bg-teal-700 px-6 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">Create Property</button>
            </div>
        </div>
    </form>

    @include('pages.properties._ai-describe-script')
</div>

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
function mapPicker(initLat = null, initLng = null, hasPin = false) {
    return {
        lat: hasPin ? initLat : null,
        lng: hasPin ? initLng : null,
        address:    @js(old('address', '')),
        city:       @js(old('city', '')),
        province:   @js(old('province', '')),
        region:     '',
        searchQuery: '',
        results:    [],
        searching:  false,
        manualLat:  '',
        manualLng:  '',
        map:        null,
        marker:     null,

        init() {
            this.$nextTick(() => {
                this.map = L.map('property-map', {
                    center: [12.8797, 121.7740],
                    zoom: 6,
                    zoomControl: true
                });

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
            this.manualLat = lat;
            this.manualLng = lng;

            const icon = L.divIcon({
                className: '',
                html: `<div style="
                    width:32px;height:32px;
                    background:#d97706;
                    border:3px solid white;
                    border-radius:50% 50% 50% 0;
                    transform:rotate(-45deg);
                    box-shadow:0 2px 8px rgba(0,0,0,0.3);
                "></div>`,
                iconSize: [32, 32],
                iconAnchor: [16, 32]
            });

            if (this.marker) {
                this.marker.setLatLng([lat, lng]);
            } else {
                this.marker = L.marker([lat, lng], { draggable: true, icon }).addTo(this.map);
                this.marker.on('dragend', (e) => {
                    const p = e.target.getLatLng();
                    this.placePin(p.lat, p.lng, true);
                });
                this.marker.on('drag', (e) => {
                    const p = e.target.getLatLng();
                    this.lat = p.lat;
                    this.lng = p.lng;
                    this.manualLat = p.lat.toFixed(6);
                    this.manualLng = p.lng.toFixed(6);
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
            } finally {
                this.searching = false;
            }
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
            // Full address: street + barangay
            const streetParts = [
                addr.house_number || '',
                addr.road || addr.pedestrian || addr.footway || addr.path || '',
                addr.suburb || addr.neighbourhood || addr.quarter || addr.village || '',
            ].filter(Boolean);
            this.address = streetParts.length
                ? streetParts.join(' ')
                : displayName.split(',').slice(0, 3).join(',').trim();

            // City/Municipality — PH cities are often in addr.city or addr.city_district
            // For municipalities, Nominatim uses addr.town or addr.municipality
            this.city = addr.city
                || addr.city_district
                || addr.town
                || addr.municipality
                || addr.county
                || addr.village
                || '';

            const { province, region } = window.philippineProvinceAndRegion(addr);
            this.province = province;
            this.region = region;

            // Update marker popup
            if (this.marker) {
                const label = [this.city, this.province, this.region].filter(Boolean).join(', ');
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
            if (isNaN(lat) || isNaN(lng)) return;
            if (lat < -90 || lat > 90 || lng < -180 || lng > 180) return;
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
