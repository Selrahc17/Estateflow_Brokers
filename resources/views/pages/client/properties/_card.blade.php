<a href="{{ route('client.property.show', $property->slug) }}"
   class="group bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-700 overflow-hidden hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 block">

    {{-- Image --}}
    <div class="relative overflow-hidden h-48 bg-stone-100 dark:bg-stone-800">
        @if($property->featured_image)
        <img src="{{ $property->featured_image }}" alt="{{ $property->name }}"
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        @else
        <div class="w-full h-full flex items-center justify-center">
            <svg class="w-12 h-12 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
        </div>
        @endif
        <div class="absolute top-3 left-3">
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-green-500 text-white">Available</span>
        </div>
        @if($property->lots_count > 0)
        <div class="absolute top-3 right-3 space-y-1 text-right">
            <span class="px-2.5 py-1 bg-white/90 backdrop-blur rounded-full text-xs font-medium text-stone-700 block">
                {{ $property->lots_count }} lot{{ $property->lots_count > 1 ? 's' : '' }} available
            </span>
            <span class="px-2.5 py-1 bg-white/90 backdrop-blur rounded-full text-xs font-medium text-stone-700 block">
                {{ $property->type ?? 'Property' }}
            </span>
        </div>
        @endif
    </div>

    {{-- Content --}}
    <div class="p-4">
        <h3 class="font-semibold text-stone-800 dark:text-white text-sm mb-1 group-hover:text-teal-700 transition">{{ $property->name }}</h3>
        <p class="text-xs text-stone-400 flex items-center gap-1 mb-3">
            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            {{ implode(', ', array_filter([$property->city, $property->province])) ?: ($property->address ?? 'Location TBA') }}
        </p>

        {{-- Specs --}}
        @if($property->bedrooms || $property->bathrooms || $property->floor_area || $property->lot_area)
        <div class="flex flex-wrap gap-x-3 gap-y-1.5 mb-3">
            @if($property->bedrooms)
            <span class="flex items-center gap-1 text-xs text-stone-500">
                <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                {{ $property->bedrooms }} bed
            </span>
            @endif
            @if($property->bathrooms)
            <span class="flex items-center gap-1 text-xs text-stone-500">
                <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m0 4a2 2 0 100 4m0-4a2 2 0 110 4m-6 4h16M4 15v4a1 1 0 001 1h14a1 1 0 001-1v-4"/></svg>
                {{ $property->bathrooms }} bath
            </span>
            @endif
            @if($property->floor_area)
            <span class="flex items-center gap-1 text-xs text-stone-500">
                <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                {{ $property->floor_area }} m²
            </span>
            @endif
            @if($property->lot_area)
            <span class="flex items-center gap-1 text-xs text-stone-500">
                <svg class="w-3.5 h-3.5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/></svg>
                {{ $property->lot_area }} m² lot
            </span>
            @endif
        </div>
        @endif

        {{-- Price --}}
        <div class="flex items-center justify-between pt-3 border-t border-stone-100 dark:border-stone-800">
            <div>
                @if($property->price)
                <p class="text-xs text-stone-400">Starting at</p>
                <p class="text-base font-bold text-teal-700">₱{{ number_format($property->price, 0) }}</p>
                @else
                <p class="text-sm font-medium text-stone-400">Price on request</p>
                @endif
            </div>
            <span class="text-xs text-teal-700 font-medium group-hover:underline">View Details →</span>
        </div>
    </div>
</a>
