@extends('layouts.broker')
@section('title', $client->full_name)
@section('page-title', $client->full_name)
@section('page-subtitle', 'Client handled by ' . ($client->broker?->name ?? 'your team'))

@section('content')
<a href="{{ route('broker.team.clients') }}" class="mb-4 inline-block text-sm font-medium text-teal-700 dark:text-teal-300 hover:underline">← Back to Team Clients</a>

<div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    <div class="h-fit rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 p-5">
        <h2 class="mb-4 font-semibold text-stone-800 dark:text-stone-100">Contact Details</h2>
        <dl class="space-y-3 text-sm">
            <div class="flex justify-between gap-3"><dt class="text-stone-500 dark:text-stone-400">Email</dt><dd class="break-all text-right text-stone-700 dark:text-stone-200">{{ $client->email }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-stone-500 dark:text-stone-400">Phone</dt><dd class="text-right text-stone-700 dark:text-stone-200">{{ $client->phone ?? '—' }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-stone-500 dark:text-stone-400">Agent</dt><dd class="text-right text-stone-700 dark:text-stone-200">{{ $client->broker?->name ?? '—' }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-stone-500 dark:text-stone-400">Requirements</dt><dd class="text-right text-stone-700 dark:text-stone-200">{{ ucfirst(str_replace('_', ' ', $client->qualification_status ?? 'pending')) }}</dd></div>
            <div class="flex justify-between gap-3"><dt class="text-stone-500 dark:text-stone-400">Added</dt><dd class="text-right text-stone-700 dark:text-stone-200">{{ $client->created_at->format('M d, Y') }}</dd></div>
        </dl>
    </div>

    <div class="space-y-5 xl:col-span-2">
        <section class="rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 p-5">
            <h2 class="mb-4 font-semibold text-stone-800 dark:text-stone-100">Reservations ({{ $client->reservations->count() }})</h2>
            <div class="space-y-3">
                @forelse($client->reservations as $reservation)
                <div class="flex items-center justify-between gap-3 border-b border-stone-100 dark:border-stone-800 pb-3 text-sm last:border-0 last:pb-0">
                    <div>
                        <p class="font-medium text-stone-700 dark:text-stone-200">{{ $reservation->lot?->property?->name ?? 'Property removed' }}</p>
                        <p class="text-xs text-stone-500 dark:text-stone-400">{{ $reservation->reservation_code }} · Lot {{ $reservation->lot?->lot_number ?? '—' }}</p>
                    </div>
                    <span class="shrink-0 text-stone-600 dark:text-stone-300">{{ ucfirst($reservation->status) }}</span>
                </div>
                @empty
                <p class="text-sm text-stone-500 dark:text-stone-400">No reservations yet.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 p-5">
            <h2 class="mb-4 font-semibold text-stone-800 dark:text-stone-100">Inquiries ({{ $inquiries->count() }})</h2>
            <div class="space-y-3">
                @forelse($inquiries as $inquiry)
                <div class="border-b border-stone-100 dark:border-stone-800 pb-3 text-sm last:border-0 last:pb-0">
                    <div class="flex items-center justify-between gap-3">
                        <p class="font-medium text-stone-700 dark:text-stone-200">{{ $inquiry->property?->name ?? 'Property removed' }}</p>
                        <span class="shrink-0 text-stone-600 dark:text-stone-300">{{ ucfirst(str_replace('_', ' ', $inquiry->status)) }}</span>
                    </div>
                    <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">{{ $inquiry->message }}</p>
                </div>
                @empty
                <p class="text-sm text-stone-500 dark:text-stone-400">No inquiries yet.</p>
                @endforelse
            </div>
        </section>

        <section class="rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 p-5">
            <h2 class="mb-4 font-semibold text-stone-800 dark:text-stone-100">Site Visits ({{ $client->siteVisits->count() }})</h2>
            <div class="space-y-3">
                @forelse($client->siteVisits as $visit)
                <div class="flex items-center justify-between gap-3 border-b border-stone-100 dark:border-stone-800 pb-3 text-sm last:border-0 last:pb-0">
                    <div>
                        <p class="font-medium text-stone-700 dark:text-stone-200">{{ $visit->property?->name ?? 'Property removed' }}</p>
                        <p class="text-xs text-stone-500 dark:text-stone-400">{{ $visit->scheduled_at?->format('M d, Y g:i A') ?? 'Not scheduled' }}</p>
                    </div>
                    <span class="shrink-0 text-stone-600 dark:text-stone-300">{{ ucfirst($visit->status) }}</span>
                </div>
                @empty
                <p class="text-sm text-stone-500 dark:text-stone-400">No site visits yet.</p>
                @endforelse
            </div>
        </section>
    </div>
</div>
@endsection
