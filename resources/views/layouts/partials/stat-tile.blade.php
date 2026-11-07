{{--
    Expects $label and $value. Optional: $icon (an SVG path) and $tone (green, amber or red),
    which colors the figure when it carries meaning. Plain counts stay neutral.
--}}
@php
    $tone = $tone ?? null;
    $icon = $icon ?? null;
    $valueColor = [
        'green' => 'text-green-700 dark:text-green-300',
        'amber' => 'text-amber-700 dark:text-amber-300',
        'red' => 'text-red-700 dark:text-red-300',
    ][$tone] ?? 'text-stone-800 dark:text-stone-100';
@endphp
<div class="flex items-center gap-3 rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 p-4">
    @if($icon)
    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-teal-50 dark:bg-teal-950 text-teal-700 dark:text-teal-300">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/></svg>
    </span>
    @endif
    <div class="min-w-0">
        <p class="text-xs font-medium text-stone-500 dark:text-stone-400">{{ $label }}</p>
        <p class="mt-1 text-xl font-bold leading-tight [overflow-wrap:anywhere] {{ $valueColor }}">{{ $value }}</p>
    </div>
</div>
