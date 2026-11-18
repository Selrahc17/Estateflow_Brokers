@extends('layouts.admin')
@section('title', 'Reservation Details')
@section('page-title', 'Reservation Details')
@section('page-subtitle', $reservation->reservation_code)

@section('content')
@php
    $agent = $reservation->broker;
    $broker = $agent?->role === 'agent' ? $agent->broker : $agent;
    $lot = $reservation->lot;
    $lotName = $lot?->label ?? 'the lot';
    $lotStyles = [
        'available' => 'bg-green-100 text-green-700 dark:bg-green-950 dark:text-green-300',
        'reserved' => 'bg-teal-100 text-teal-800 dark:bg-teal-950 dark:text-teal-300',
        'sold' => 'bg-red-100 text-red-700 dark:bg-red-950 dark:text-red-300',
    ];
    $statusNote = [
        'requested' => 'The buyer asked for this lot. It is not held for them until someone confirms it.',
        'pending' => "{$lotName} is held for this client while the reservation is pending.",
        'confirmed' => "{$lotName} is reserved for this client.",
        'completed' => 'This sale is complete and can no longer be changed.',
        'cancelled' => 'This reservation was cancelled and no longer holds the lot.',
    ][$reservation->status] ?? '';
    $actionDetails = [
        'confirmed' => [
            'label' => $reservation->status === 'requested' ? 'Confirm request' : 'Confirm reservation',
            'help' => "Reserves {$lotName} for this client.",
            'class' => 'bg-teal-700 text-white hover:bg-teal-800',
            'confirm' => null,
        ],
        'completed' => [
            'label' => 'Mark as sold',
            'help' => "Completes the sale and marks {$lotName} as sold. This cannot be undone.",
            'class' => 'bg-green-600 text-white hover:bg-green-700',
            'confirm' => "Mark {$lotName} as sold? A completed sale cannot be changed later.",
        ],
        'cancelled' => [
            'label' => $reservation->status === 'requested' ? 'Decline request' : 'Cancel reservation',
            'help' => $reservation->status === 'requested' ? 'Turns down the buyer\'s request.' : "Frees {$lotName} so others can reserve it.",
            'class' => 'border border-red-200 text-red-700 hover:bg-red-50 dark:border-red-900 dark:text-red-300 dark:hover:bg-red-950',
            'confirm' => $reservation->status === 'requested' ? 'Decline this request?' : "Cancel this reservation? {$lotName} will become available again.",
        ],
        'pending' => [
            'label' => 'Restore as pending',
            'help' => "Holds {$lotName} for this client again, if it is still available.",
            'class' => 'border border-stone-200 text-stone-700 hover:bg-stone-50 dark:border-stone-700 dark:text-stone-200 dark:hover:bg-stone-800',
            'confirm' => null,
        ],
    ];
    $card = 'rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900';
@endphp

<a href="{{ route('admin.reservations') }}" class="mb-4 inline-flex items-center gap-1 text-sm font-medium text-stone-600 hover:text-teal-700 dark:text-stone-300 dark:hover:text-teal-400">
    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
    All reservations
</a>

@if(session('success'))
<div class="mb-5 rounded-lg border border-green-200 dark:border-green-900 bg-green-50 dark:bg-green-950 px-4 py-3 text-sm text-green-700 dark:text-green-300" role="status">{{ session('success') }}</div>
@endif
@if($errors->any())
<div role="alert" class="mb-5 rounded-lg border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950 px-4 py-3 text-sm text-red-700 dark:text-red-300">{{ $errors->first() }}</div>
@endif

{{-- Summary --}}
<section class="{{ $card }} mb-5 p-5" aria-label="Reservation summary">
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex flex-wrap items-center gap-2">
            <h2 class="font-mono text-lg font-bold text-stone-800 dark:text-stone-100">{{ $reservation->reservation_code }}</h2>
            @include('pages.admin.reservations._status-badge')
        </div>
        <p class="text-xs text-stone-500 dark:text-stone-400">Created {{ $reservation->created_at->format('M d, Y g:i A') }}</p>
    </div>
    <dl class="mt-4 grid grid-cols-2 gap-4 border-t border-stone-100 dark:border-stone-800 pt-4 lg:grid-cols-4">
        <div>
            <dt class="text-xs text-stone-500 dark:text-stone-400">Total price</dt>
            <dd class="mt-0.5 text-lg font-bold text-stone-800 dark:text-stone-100">₱{{ number_format($reservation->total_price, 2) }}</dd>
        </div>
        <div>
            <dt class="text-xs text-stone-500 dark:text-stone-400">Reservation fee paid</dt>
            <dd class="mt-0.5 text-lg font-semibold text-stone-800 dark:text-stone-100">{{ $reservation->reservation_fee !== null ? '₱'.number_format($reservation->reservation_fee, 2) : 'Not recorded' }}</dd>
        </div>
        <div>
            <dt class="text-xs text-stone-500 dark:text-stone-400">Reserved on</dt>
            <dd class="mt-0.5 font-medium text-stone-700 dark:text-stone-200">{{ $reservation->reserved_at?->format('M d, Y') ?? '—' }}</dd>
        </div>
        <div>
            <dt class="text-xs text-stone-500 dark:text-stone-400">Expires</dt>
            <dd class="mt-0.5 font-medium {{ $reservation->expires_at?->isPast() && $reservation->holdsLot() ? 'text-red-700 dark:text-red-400' : 'text-stone-700 dark:text-stone-200' }}">
                {{ $reservation->expires_at?->format('M d, Y') ?? '—' }}
                @if($reservation->expires_at?->isPast() && $reservation->holdsLot())<span class="block text-xs font-normal">Past the expiry date</span>@endif
            </dd>
        </div>
    </dl>
</section>

<div class="grid grid-cols-1 gap-5 xl:grid-cols-3">
    <div class="space-y-5 xl:col-span-2">
        {{-- People --}}
        <section class="{{ $card }} p-5" aria-labelledby="people-heading">
            <h2 id="people-heading" class="mb-4 font-semibold text-stone-800 dark:text-stone-100">People</h2>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-stone-500 dark:text-stone-400">Client</p>
                    <p class="mt-1 font-medium text-stone-800 dark:text-stone-100">{{ $reservation->client?->full_name ?? '—' }}</p>
                    @if($reservation->client?->email)<a href="mailto:{{ $reservation->client->email }}" class="block break-all text-xs text-teal-700 hover:underline dark:text-teal-400">{{ $reservation->client->email }}</a>@endif
                    @if($reservation->client?->phone)<a href="tel:{{ preg_replace('/[^0-9+]/', '', $reservation->client->phone) }}" class="block text-xs text-stone-500 hover:underline dark:text-stone-400">{{ $reservation->client->phone }}</a>@endif
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-stone-500 dark:text-stone-400">Agent</p>
                    <p class="mt-1 font-medium text-stone-800 dark:text-stone-100">{{ $agent?->name ?? '—' }}</p>
                    @if($agent?->email)<a href="mailto:{{ $agent->email }}" class="block break-all text-xs text-teal-700 hover:underline dark:text-teal-400">{{ $agent->email }}</a>@endif
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-stone-500 dark:text-stone-400">Broker</p>
                    <p class="mt-1 font-medium text-stone-800 dark:text-stone-100">{{ $broker?->name ?? '—' }}</p>
                    @if($broker?->email)<a href="mailto:{{ $broker->email }}" class="block break-all text-xs text-teal-700 hover:underline dark:text-teal-400">{{ $broker->email }}</a>@endif
                </div>
            </div>
        </section>

        {{-- Property and lot --}}
        <section class="{{ $card }} p-5" aria-labelledby="lot-heading">
            <div class="mb-4 flex items-center justify-between gap-3">
                <h2 id="lot-heading" class="font-semibold text-stone-800 dark:text-stone-100">Property and lot</h2>
                @if($lot?->property?->slug)
                <a href="{{ route('client.property.show', $lot->property->slug) }}" target="_blank" rel="noopener" class="text-xs font-semibold text-teal-700 hover:underline dark:text-teal-400">View listing</a>
                @endif
            </div>
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div class="sm:col-span-2">
                    <p class="text-xs text-stone-500 dark:text-stone-400">Property</p>
                    <p class="mt-0.5 font-medium text-stone-800 dark:text-stone-100">{{ $lot?->property?->name ?? 'Property removed' }}</p>
                    @if($lot?->property?->city || $lot?->property?->province)
                    <p class="text-xs text-stone-500 dark:text-stone-400">{{ collect([$lot->property->city, $lot->property->province])->filter()->implode(', ') }}</p>
                    @endif
                </div>
                <div>
                    <p class="text-xs text-stone-500 dark:text-stone-400">Lot</p>
                    <p class="mt-0.5 flex flex-wrap items-center gap-2 font-medium text-stone-800 dark:text-stone-100">
                        {{ $lot?->label ?? '—' }}
                        @if($lot)<span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $lotStyles[$lot->status] ?? 'bg-stone-100 text-stone-600' }}">{{ ucfirst($lot->status) }}</span>@endif
                    </p>
                </div>
            </div>
        </section>

        @if($reservation->notes)
        <section class="{{ $card }} p-5" aria-labelledby="notes-heading">
            <h2 id="notes-heading" class="mb-2 font-semibold text-stone-800 dark:text-stone-100">Notes</h2>
            <p class="whitespace-pre-line text-sm text-stone-600 dark:text-stone-300">{{ $reservation->notes }}</p>
        </section>
        @endif
    </div>

    {{-- Actions --}}
    <aside class="{{ $card }} h-fit p-5" aria-labelledby="actions-heading">
        <h2 id="actions-heading" class="font-semibold text-stone-800 dark:text-stone-100">Status</h2>
        <p class="mt-1 text-sm text-stone-600 dark:text-stone-300">{{ $statusNote }}</p>

        @if($actions)
        <div class="mt-4 space-y-3">
            @foreach($actions as $target)
            @php $action = $actionDetails[$target]; @endphp
            <form action="{{ route('admin.reservations.status', $reservation) }}" method="POST" @if($action['confirm']) onsubmit="return confirm(@js($action['confirm']))" @endif>
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="{{ $target }}">
                <button type="submit" class="w-full rounded-lg px-4 py-2 text-sm font-semibold transition {{ $action['class'] }}">{{ $action['label'] }}</button>
                <p class="mt-1 text-xs text-stone-500 dark:text-stone-400">{{ $action['help'] }}</p>
            </form>
            @endforeach
        </div>
        <p class="mt-4 border-t border-stone-100 dark:border-stone-800 pt-3 text-xs text-stone-500 dark:text-stone-400">The agent and the client are notified of any change.</p>
        @endif
    </aside>
</div>
@endsection
