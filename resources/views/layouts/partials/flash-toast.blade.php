{{--
    A short pop-up after an action, such as "Inquiry sent!". Shows the page's
    success or error message, closes on its own, and can be dismissed.
    Pages that show their own banner can turn it off with @section('hide-flash-toast', '1').
--}}
@php
    $toast = session('success') ? ['success', session('success')] : (session('error') ? ['error', session('error')] : null);
    [$toastType, $toastMessage] = $toast ?? [null, null];
@endphp
@if($toast && ! trim($__env->yieldContent('hide-flash-toast')))
<div x-data="{ open: true }" x-init="setTimeout(() => open = false, {{ $toastType === 'success' ? 6000 : 9000 }})" x-show="open"
     x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
     x-transition:leave="transition ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
     class="fixed inset-x-4 bottom-4 z-[100] sm:inset-x-auto sm:right-6 sm:bottom-6 sm:w-96"
     role="{{ $toastType === 'success' ? 'status' : 'alert' }}" aria-live="{{ $toastType === 'success' ? 'polite' : 'assertive' }}" data-flash-toast>
    <div class="flex items-start gap-3 rounded-2xl border p-4 shadow-xl {{ $toastType === 'success' ? 'border-green-200 bg-white dark:border-green-900 dark:bg-stone-900' : 'border-red-200 bg-white dark:border-red-900 dark:bg-stone-900' }}">
        <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $toastType === 'success' ? 'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-300' : 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300' }}" aria-hidden="true">
            @if($toastType === 'success')
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
            @else
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
            @endif
        </span>
        <div class="min-w-0 flex-1 pt-0.5">
            <p class="text-sm font-semibold {{ $toastType === 'success' ? 'text-stone-800 dark:text-stone-100' : 'text-red-800 dark:text-red-200' }}">{{ $toastType === 'success' ? 'Done' : 'Something went wrong' }}</p>
            <p class="mt-0.5 text-sm text-stone-600 dark:text-stone-300">{{ $toastMessage }}</p>
        </div>
        <button type="button" @click="open = false" class="-mr-1 -mt-1 rounded-lg p-1 text-stone-400 hover:bg-stone-100 hover:text-stone-600 dark:hover:bg-stone-800" aria-label="Close">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
    </div>
</div>
@endif
