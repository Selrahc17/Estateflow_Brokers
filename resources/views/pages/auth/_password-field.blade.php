{{-- Expects $name and $label. Optional: $autocomplete, and $attrs for trusted Alpine attributes. --}}
@php($hasError = $errors->has($name))
<div x-data="{ show: false }">
    <label for="{{ $name }}" class="text-sm text-stone-600 dark:text-stone-300 font-medium mb-1 block">{{ $label }}</label>
    <div class="relative">
        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-stone-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
        <input id="{{ $name }}" type="password" :type="show ? 'text' : 'password'" name="{{ $name }}" placeholder="••••••••" required
            autocomplete="{{ $autocomplete ?? 'current-password' }}"
            @if($hasError) aria-invalid="true" aria-describedby="{{ $name }}-error" @endif
            {!! $attrs ?? '' !!}
            class="w-full border {{ $hasError ? 'border-red-300 dark:border-red-500' : 'border-stone-200 dark:border-stone-700' }} bg-stone-50 dark:bg-stone-800 text-stone-800 dark:text-stone-100 placeholder:text-stone-400 dark:placeholder:text-stone-500 rounded-xl pl-10 pr-11 py-2.5 text-sm transition focus:outline-none focus:bg-white dark:focus:bg-stone-800 focus:ring-2 focus:ring-teal-400">
        <button type="button" @click="show = !show" :aria-label="show ? 'Hide password' : 'Show password'" aria-label="Show password"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-stone-400 hover:text-teal-700 dark:hover:text-teal-400 transition">
            <svg x-show="!show" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            <svg x-show="show" style="display: none" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.477 0-8.268-2.943-9.542-7a9.97 9.97 0 012.163-3.592M6.343 6.343A9.97 9.97 0 0112 5c4.477 0 8.268 2.943 9.542 7a9.97 9.97 0 01-1.357 2.498M6.343 6.343L3 3m3.343 3.343l11.314 11.314M17.657 17.657L21 21"/>
            </svg>
        </button>
    </div>
    @error($name)<p id="{{ $name }}-error" class="text-xs text-red-600 dark:text-red-400 mt-1">{{ $message }}</p>@enderror
</div>
