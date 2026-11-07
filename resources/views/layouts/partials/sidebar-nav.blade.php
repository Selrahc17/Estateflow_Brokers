{{--
    Expects $label (for screen readers) and $sections, a list of:
    ['title' => string|null, 'links' => [['route', 'active', 'label', 'icon', 'badge' => optional count, 'badgeColor' => optional bg class]]]
    Needs the layout's sidebarOpen Alpine state, which also drives the collapsed icon rail.
--}}
<nav aria-label="{{ $label }}" class="min-h-0 flex-1 overflow-y-auto py-5 [color-scheme:dark]" :class="sidebarOpen ? 'px-3' : 'px-2'">
    @foreach($sections as $section)
    <div class="{{ $loop->first ? '' : 'mt-6' }} space-y-1">
        @if($section['title'])
        <p x-show="sidebarOpen" class="px-3 pb-2 text-xs font-semibold uppercase tracking-widest text-[var(--color-primary-light)]">{{ $section['title'] }}</p>
        @endif
        @unless($loop->first)
        <div x-show="!sidebarOpen" class="mx-2 mb-2 border-t border-[var(--color-primary-dark)]" aria-hidden="true"></div>
        @endunless
        @foreach($section['links'] as $link)
        @php($isActive = request()->routeIs(...(array) $link['active']))
        @php($badge = $link['badge'] ?? 0)
        @php($badgeColor = $link['badgeColor'] ?? 'bg-red-600')
        <a href="{{ route($link['route']) }}" @if($isActive) aria-current="page" @endif
            :title="sidebarOpen ? null : @js($link['label'])"
            :aria-label="sidebarOpen ? null : @js($link['label'])"
            :class="sidebarOpen ? '' : 'justify-center'"
            class="relative flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition {{ $isActive ? 'bg-[var(--color-primary)] text-white' : 'text-teal-100 hover:bg-[var(--color-sidebar-hover)] hover:text-white' }}">
            <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $link['icon'] }}"/></svg>
            <span x-show="sidebarOpen" class="min-w-0 flex-1">{{ $link['label'] }}</span>
            @if($badge > 0)
            <span x-show="sidebarOpen" class="shrink-0 rounded-full {{ $badgeColor }} px-1.5 text-xs font-semibold leading-5 text-white">{{ $badge > 99 ? '99+' : $badge }}</span>
            <span x-show="!sidebarOpen" class="absolute right-2 top-2 h-2 w-2 rounded-full {{ $badgeColor }}" aria-hidden="true"></span>
            @endif
        </a>
        @endforeach
    </div>
    @endforeach
</nav>
