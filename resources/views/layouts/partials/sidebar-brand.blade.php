{{-- Expects $portal and $appLogo. Needs the layout's sidebarOpen Alpine state. --}}
<div class="shrink-0 border-b border-[var(--color-primary-dark)] py-5" :class="sidebarOpen ? 'px-5' : 'px-4'">
    <div class="mb-1 flex items-center gap-2">
        <div class="flex h-8 w-8 shrink-0 items-center justify-center overflow-hidden rounded-lg bg-[var(--color-primary)]">
            @if($appLogo)
                <img src="{{ $appLogo }}" alt="" class="h-full w-full object-cover">
            @else
                <svg class="h-5 w-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
            @endif
        </div>
        <p x-show="sidebarOpen" class="text-lg font-bold tracking-wide text-[var(--color-accent)]">EstateFlow</p>
    </div>
    <p x-show="sidebarOpen" class="mt-1 text-xs font-semibold uppercase tracking-widest text-[var(--color-primary-light)]">{{ $portal }}</p>
</div>
