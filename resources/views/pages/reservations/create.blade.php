@extends('layouts.app')
@section('title', 'New Reservation')
@section('page-title', 'New Reservation')
@section('page-subtitle', 'Create a reservation for a client')

@section('content')
@php
    // Each available lot's price, so choosing a lot fills in the total price.
    $lotPrices = $lotsByProject->flatten(1)->mapWithKeys(fn ($lot) => [$lot->id => (float) ($lot->price ?? $lot->property?->price ?? 0)]);
    $chosenClient = old('client_id', request('client_id'));
    $chosenLot = old('lot_id', request('lot_id'));
@endphp
<div class="max-w-2xl">
    @if($clients->isEmpty() || $lotsByProject->isEmpty())
    <div class="mb-4 space-y-2 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900" role="status">
        @if($clients->isEmpty())
        <p><span class="font-semibold">You have no clients yet.</span> Add the buyer first: open their inquiry and choose <span class="font-medium">Add as Client</span>, or <a href="{{ route('agent.clients.create') }}" class="font-semibold underline">add a client</a> yourself.</p>
        @endif
        @if($lotsByProject->isEmpty())
        <p><span class="font-semibold">No lots are available to reserve.</span> Reservations are made on a lot or unit. Add lots in <a href="{{ route('agent.lots.index') }}" class="font-semibold underline">Lot Availability</a>, or check that some are still Available.</p>
        @endif
    </div>
    @endif
    <div class="bg-white rounded-xl border border-stone-200 p-6">
        <form action="{{ route('agent.reservations.store') }}" method="POST" class="space-y-4"
              x-data="{ prices: @js($lotPrices), lot: @js((string) $chosenLot), price: @js(old('total_price', $chosenLot ? ($lotPrices[$chosenLot] ?? '') : '')) }">
            @csrf
            <div>
                <label for="client_id" class="block text-sm font-medium text-stone-700 mb-1">Client</label>
                <select id="client_id" name="client_id" required class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                    <option value="">{{ $clients->isEmpty() ? 'No clients yet' : 'Select client...' }}</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" @selected((string) $chosenClient === (string) $client->id)>{{ $client->full_name }} — {{ $client->email }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-xs text-stone-500">Any of your clients. <a href="{{ route('agent.clients.create') }}" class="text-teal-700 hover:underline">Add a new client</a></p>
                @error('client_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="lot_id" class="block text-sm font-medium text-stone-700 mb-1">Lot / Unit</label>
                <select id="lot_id" name="lot_id" required x-model="lot" @change="price = prices[lot] || price" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                    <option value="">{{ $lotsByProject->isEmpty() ? 'No available lots' : 'Select lot...' }}</option>
                    @foreach($lotsByProject as $project => $projectLots)
                    <optgroup label="{{ $project }} ({{ $projectLots->count() }} available)">
                        @foreach($projectLots as $lot)
                        <option value="{{ $lot->id }}" @selected((string) $chosenLot === (string) $lot->id)>{{ $lot->lot_number }}{{ $lot->square_meters ? ' · ' . rtrim(rtrim(number_format((float) $lot->square_meters, 2), '0'), '.') . ' sqm' : '' }}{{ $lot->price ? ' · ₱' . number_format((float) $lot->price, 0) : '' }}</option>
                        @endforeach
                    </optgroup>
                    @endforeach
                </select>
                @error('lot_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="total_price" class="block text-sm font-medium text-stone-700 mb-1">Total Price (₱)</label>
                    <input id="total_price" type="number" name="total_price" x-model="price" value="{{ old('total_price') }}" step="0.01" min="0" required class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                    <p class="mt-1 text-xs text-stone-500">Filled in from the lot's price. You can change it.</p>
                    @error('total_price')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="reservation_fee" class="block text-sm font-medium text-stone-700 mb-1">Reservation Fee Paid (₱) <span class="text-xs font-normal text-stone-500">(optional)</span></label>
                    <input id="reservation_fee" type="number" name="reservation_fee" value="{{ old('reservation_fee') }}" step="0.01" min="0" placeholder="e.g. 10000" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                    @error('reservation_fee')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <p class="rounded-lg bg-stone-50 px-3 py-2 text-xs text-stone-500">Downpayment, financing and the payment schedule are handled by the developer, so they aren't recorded here.</p>
            <div>
                <label for="notes" class="block text-sm font-medium text-stone-700 mb-1">Notes</label>
                <textarea id="notes" name="notes" rows="3" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">{{ old('notes') }}</textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-teal-700 hover:bg-teal-800 text-white px-5 py-2 rounded-lg text-sm font-medium transition">Create Reservation</button>
                <a href="{{ route('agent.reservations.index') }}" class="px-5 py-2 rounded-lg text-sm font-medium border border-stone-200 text-stone-600 hover:bg-stone-50 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
