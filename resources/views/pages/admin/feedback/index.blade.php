@extends('layouts.admin')
@section('title', 'Feedback Management')
@section('page-title', 'Feedback Management')
@section('page-subtitle', 'Review and resolve client feedback and ratings')

@section('content')
@if(session('success'))
<div class="mb-5 rounded-lg border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-950 px-4 py-3 text-sm text-green-700 dark:text-green-300">{{ session('success') }}</div>
@endif

<div class="mb-5 grid grid-cols-2 gap-4 lg:grid-cols-4">
    @foreach([
        ['Total Feedback', $stats['total']],
        ['Average Rating', $stats['average'] ? number_format($stats['average'], 1).' ★' : '—'],
        ['Pending', $stats['pending']],
        ['Resolved', $stats['resolved']],
    ] as [$label, $value])
        @include('layouts.partials.stat-tile', compact('label', 'value'))
    @endforeach
</div>

<form method="GET" class="mb-5 flex flex-wrap gap-2">
    <select name="status" aria-label="Filter by status" onchange="this.form.submit()" class="rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
        <option value="">All statuses</option>
        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
        <option value="resolved" @selected(request('status') === 'resolved')>Resolved</option>
    </select>
    <select name="rating" aria-label="Filter by rating" onchange="this.form.submit()" class="rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
        <option value="">All ratings</option>
        @foreach([5, 4, 3, 2, 1] as $rating)
        <option value="{{ $rating }}" @selected((int) request('rating') === $rating)>{{ $rating }} star{{ $rating > 1 ? 's' : '' }}</option>
        @endforeach
    </select>
</form>

<div class="space-y-3">
    @forelse($feedbacks as $feedback)
    <div class="rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 p-5">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div class="min-w-0">
                <p class="font-semibold text-stone-800 dark:text-stone-100">{{ $feedback->subject ?: ucfirst((string) $feedback->type) ?: 'Feedback' }}</p>
                <p class="text-xs text-stone-500 dark:text-stone-400">
                    {{ $feedback->client?->full_name ?? $feedback->user?->name ?? 'Unknown client' }} · {{ $feedback->created_at->format('M d, Y') }}
                    @if($feedback->rating) · {{ $feedback->rating }} ★ @endif
                </p>
            </div>
            <div class="flex items-center gap-2">
                <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $feedback->status === 'resolved' ? 'bg-green-100 dark:bg-green-950 text-green-700 dark:text-green-300' : 'bg-yellow-100 dark:bg-yellow-950 text-yellow-800 dark:text-yellow-300' }}">{{ ucfirst($feedback->status) }}</span>
                @if($feedback->status !== 'resolved')
                <form action="{{ route('admin.feedback.resolve', $feedback) }}" method="POST">
                    @csrf
                    <button type="submit" class="rounded-lg bg-stone-100 dark:bg-stone-800 px-2 py-1 text-xs font-medium text-stone-700 dark:text-stone-200 transition hover:bg-stone-200 dark:hover:bg-stone-700">Mark Resolved</button>
                </form>
                @endif
            </div>
        </div>
        <p class="mt-3 text-sm text-stone-600 dark:text-stone-300">{{ $feedback->message }}</p>
    </div>
    @empty
    <div class="rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 py-14 text-center text-sm text-stone-500 dark:text-stone-400">No feedback found.</div>
    @endforelse
</div>

@if($feedbacks->hasPages())
<div class="mt-5">{{ $feedbacks->links() }}</div>
@endif
@endsection
