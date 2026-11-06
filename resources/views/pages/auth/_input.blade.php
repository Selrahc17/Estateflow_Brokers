{{-- Expects $name and $label. Optional: $type, $value, $icon, $placeholder, $autocomplete, and $attrs for trusted Alpine or HTML attributes. --}}
@php
    $iconPaths = [
        'user' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        'mail' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'phone' => 'M3 5a2 2 0 012-2h3.28a1 1 0 01.95.68l1.5 4.5a1 1 0 01-.5 1.21l-2.26 1.13a11.04 11.04 0 005.52 5.52l1.13-2.26a1 1 0 011.21-.5l4.5 1.5a1 1 0 01.68.95V19a2 2 0 01-2 2h-1C9.72 21 3 14.28 3 6V5z',
        'badge' => 'M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.31 0 2.42.84 2.83 2M9 14a3 3 0 00-2.83 2M15 11h3m-3 4h2',
        'calendar' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'hash' => 'M7 20l4-16m2 16l4-16M6 9h14M4 15h14',
    ];
    $hasError = $errors->has($name);
@endphp
<div>
    <label for="{{ $name }}" class="text-sm text-stone-600 dark:text-stone-300 font-medium mb-1 block">{{ $label }}</label>
    <div class="relative">
        @isset($iconPaths[$icon ?? ''])
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $iconPaths[$icon] }}"/></svg>
        @endisset
        <input id="{{ $name }}" type="{{ $type ?? 'text' }}" name="{{ $name }}" value="{{ old($name, $value ?? null) }}"
            @isset($placeholder) placeholder="{{ $placeholder }}" @endisset
            @isset($autocomplete) autocomplete="{{ $autocomplete }}" @endisset
            @if($hasError) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
            {!! $attrs ?? '' !!}
            class="w-full border {{ $hasError ? 'border-red-300 dark:border-red-500' : 'border-stone-200 dark:border-stone-700' }} bg-stone-50 dark:bg-stone-800 text-stone-800 dark:text-stone-100 placeholder:text-stone-400 dark:placeholder:text-stone-500 rounded-xl {{ isset($iconPaths[$icon ?? '']) ? 'pl-10' : 'pl-4' }} pr-4 py-2.5 text-sm transition focus:outline-none focus:bg-white dark:focus:bg-stone-800 focus:ring-2 focus:ring-teal-400">
    </div>
    @error($name)<p id="{{ $name }}-error" class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
</div>
