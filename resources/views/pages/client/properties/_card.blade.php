<a href="{{ route('client.property.show', $property->slug) }}"
   class="group bg-white dark:bg-stone-900 rounded-2xl border border-stone-200 dark:border-stone-700 overflow-hidden hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 block">

    {{-- Image --}}
    <div class="relative overflow-hidden h-48 bg-stone-100 dark:bg-stone-800">
        @if($property->featured_image)
        <img src="{{ asset('storage/' . $property->featured_image) }}" alt="{{ $property->name }}"
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
        <div class="absolute top-3 right-3">
            <span class="px-2.5 py-1 bg-white/90 backdrop-blur rounded-full text-xs font-medium text-stone-700">
                {{ $property->lots_count }} lot{{ $property->lots_count > 1 ? 's' : '' }} available
            </span>
        </div>
        @endif
    </div>

    {{-- Content --}}
    <div class="p-4">
        <h3 class="font-semibold text-stone-800 dark:text-white text-sm mb-1 group-hover:text-amber-600 transition">{{ $property->name }}</h3>
        <p class="text-xs text-stone-400 flex items-center gap-1 mb-3">
            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            {{ implode(', ', array_filter([$property->city, $property->province])) ?: ($property->address ?? 'Location TBA') }}
        </p>

        {{-- Price --}}
        <div class="flex items-center justify-between pt-3 border-t border-stone-100 dark:border-stone-800">
            <div>
                @if($property->price)
                <p class="text-xs text-stone-400">Starting at</p>
                <p class="text-base font-bold text-amber-600">₱{{ number_format($property->price, 0) }}</p>
                @else
                <p class="text-sm font-medium text-stone-400">Price on request</p>
                @endif
            </div>
            <span class="text-xs text-amber-600 font-medium group-hover:underline">View Details →</span>
        </div>
    </div>
</a>
