@extends('layouts.broker')
@section('title', 'Team Reservations')
@section('page-title', 'Team Reservations')
@section('page-subtitle', 'Reservations handled by your Agents')

@section('content')
@include('pages.broker.team._filters', [
    'statuses' => ['pending' => 'Pending', 'confirmed' => 'Confirmed', 'completed' => 'Completed', 'cancelled' => 'Cancelled'],
    'searchPlaceholder' => 'Search code, client name, or email...',
])

<div class="overflow-hidden rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900">
    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="border-b border-stone-100 dark:border-stone-800 bg-stone-50 dark:bg-stone-800/60">
                <tr class="text-left text-stone-500 dark:text-stone-400">
                    <th class="px-5 py-3 font-medium">Code</th>
                    <th class="px-5 py-3 font-medium">Client</th>
                    <th class="px-5 py-3 font-medium">Property</th>
                    <th class="px-5 py-3 font-medium">Agent</th>
                    <th class="px-5 py-3 font-medium">Price</th>
                    <th class="px-5 py-3 font-medium">Status</th>
                    <th class="px-5 py-3 font-medium">Reserved</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-stone-100 dark:divide-stone-800">
                @forelse($reservations as $reservation)
                <tr class="transition hover:bg-stone-50 dark:hover:bg-stone-800">
                    <td class="px-5 py-3 font-mono text-xs text-stone-600 dark:text-stone-300">{{ $reservation->reservation_code }}</td>
                    <td class="px-5 py-3">
                        @if($reservation->client)
                            <a href="{{ route('broker.team.clients.show', $reservation->client) }}" class="font-medium text-teal-700 dark:text-teal-300 hover:underline">{{ $reservation->client->full_name }}</a>
                        @else
                            <span class="text-stone-500 dark:text-stone-400">—</span>
                        @endif
                    </td>
                    <td class="px-5 py-3 text-stone-600 dark:text-stone-300">
                        <p>{{ $reservation->lot?->property?->name ?? 'Property removed' }}</p>
                        <p class="text-xs text-stone-500 dark:text-stone-400">Lot {{ $reservation->lot?->lot_number ?? '—' }}</p>
                    </td>
                    <td class="px-5 py-3 text-stone-600 dark:text-stone-300">{{ $reservation->broker?->name ?? '—' }}</td>
                    <td class="px-5 py-3 text-stone-600 dark:text-stone-300">₱{{ number_format((float) $reservation->total_price, 0) }}</td>
                    <td class="px-5 py-3 text-stone-600 dark:text-stone-300">{{ ucfirst($reservation->status) }}</td>
                    <td class="px-5 py-3 text-xs text-stone-500 dark:text-stone-400">{{ $reservation->reserved_at?->format('M d, Y') ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="7" class="px-5 py-10 text-center text-stone-500 dark:text-stone-400">No reservations found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($reservations->hasPages())
        <div class="border-t border-stone-100 dark:border-stone-800 px-5 py-4">{{ $reservations->links() }}</div>
    @endif
</div>
@endsection
