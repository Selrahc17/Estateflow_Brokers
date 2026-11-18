@php
    $statusStyles = [
        'requested' => 'bg-violet-100 text-violet-800 dark:bg-violet-950 dark:text-violet-300',
        'pending' => 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300',
        'confirmed' => 'bg-teal-100 text-teal-800 dark:bg-teal-950 dark:text-teal-300',
        'completed' => 'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-300',
        'cancelled' => 'bg-stone-200 text-stone-600 dark:bg-stone-800 dark:text-stone-400',
    ];
@endphp
<span class="inline-flex whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-medium {{ $statusStyles[$reservation->status] ?? $statusStyles['cancelled'] }}">{{ $reservation->status_label }}</span>
