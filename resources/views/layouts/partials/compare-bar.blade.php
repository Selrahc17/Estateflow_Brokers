{{-- A small link back to the comparison while the visitor has picked any listings. --}}
@php $compareCount = count(session('compare', [])); @endphp
@if($compareCount && ! request()->routeIs('client.compare'))
<a href="{{ route('client.compare') }}" data-compare-bar
   class="fixed bottom-6 left-4 z-40 inline-flex items-center gap-2 rounded-full bg-stone-900 px-4 py-3 text-sm font-semibold text-white shadow-xl transition hover:bg-teal-800 dark:bg-teal-700 dark:hover:bg-teal-600">
    <span aria-hidden="true">⇄</span>
    {{ $compareCount >= 2 ? 'Compare' : 'Compare list' }}
    <span class="flex h-5 min-w-5 items-center justify-center rounded-full bg-white px-1.5 text-xs font-bold text-stone-900">{{ $compareCount }}</span>
</a>
@endif
