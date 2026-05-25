<a href="{{ route('client.property.show', ['slug' => \Illuminate\Support\Str::slug($p[0])]) }}"
   class="group bg-white rounded-2xl border border-stone-200 overflow-hidden hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 block">

    {{-- Image --}}
    <div class="relative overflow-hidden h-48">
        <img src="{{ $p[8] }}" alt="{{ $p[0] }}"
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
        {{-- Status Badge --}}
        <div class="absolute top-3 left-3">
            <span class="px-2.5 py-1 rounded-full text-xs font-semibold
                {{ $p[3] === 'Pre-Selling' ? 'bg-amber-500 text-white' : '' }}
                {{ $p[3] === 'RFO' ? 'bg-green-500 text-white' : '' }}
                {{ $p[3] === 'Available' ? 'bg-blue-500 text-white' : '' }}">
                {{ $p[3] }}
            </span>
        </div>
        {{-- Type Badge --}}
        <div class="absolute top-3 right-3">
            <span class="px-2.5 py-1 bg-white/90 backdrop-blur rounded-full text-xs font-medium text-stone-700">
                {{ $p[2] }}
            </span>
        </div>
    </div>

    {{-- Content --}}
    <div class="p-4">
        <h3 class="font-semibold text-stone-800 text-sm mb-1 group-hover:text-amber-600 transition">{{ $p[0] }}</h3>
        <p class="text-xs text-stone-400 flex items-center gap-1 mb-3">
            <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
            {{ $p[1] }}
        </p>

        {{-- Specs --}}
        <div class="flex items-center gap-3 text-xs text-stone-500 mb-3">
            <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/></svg>
                {{ $p[5] }}
            </span>
            @if($p[6] !== '—')
            <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                {{ $p[6] }} bed
            </span>
            <span class="flex items-center gap-1">
                <svg class="w-3.5 h-3.5 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                {{ $p[7] }} bath
            </span>
            @endif
        </div>

        {{-- Price --}}
        <div class="flex items-center justify-between pt-3 border-t border-stone-100">
            <div>
                <p class="text-xs text-stone-400">Starting at</p>
                <p class="text-base font-bold text-amber-600">{{ $p[4] }}</p>
            </div>
            <span class="text-xs text-amber-600 font-medium group-hover:underline">View Details →</span>
        </div>
    </div>
</a>
