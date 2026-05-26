@extends('layouts.app')
@section('title', 'New Reservation')
@section('page-title', 'New Reservation')
@section('page-subtitle', 'Create a reservation for a client')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-xl border border-stone-200 p-6">
        <form action="{{ route('broker.reservations.store') }}" method="POST" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Client</label>
                <select name="client_id" required class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <option value="">Select client...</option>
                    @foreach($clients as $client)
                    <option value="{{ $client->id }}" {{ old('client_id') == $client->id ? 'selected' : '' }}>{{ $client->full_name }} — {{ $client->email }}</option>
                    @endforeach
                </select>
                @error('client_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Lot</label>
                <select name="lot_id" required class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    <option value="">Select lot...</option>
                    @foreach($lots as $lot)
                    <option value="{{ $lot->id }}" {{ old('lot_id') == $lot->id ? 'selected' : '' }}>{{ $lot->property?->name }} — Lot {{ $lot->lot_number }} (₱{{ number_format($lot->price, 2) }})</option>
                    @endforeach
                </select>
                @error('lot_id')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Total Price</label>
                    <input type="number" name="total_price" value="{{ old('total_price') }}" step="0.01" min="0" required class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    @error('total_price')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Down Payment</label>
                    <input type="number" name="down_payment" value="{{ old('down_payment') }}" step="0.01" min="0" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Payment Schedule</label>
                    <select name="payment_schedule" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                        <option value="monthly" {{ old('payment_schedule') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                        <option value="quarterly" {{ old('payment_schedule') === 'quarterly' ? 'selected' : '' }}>Quarterly</option>
                        <option value="annual" {{ old('payment_schedule') === 'annual' ? 'selected' : '' }}>Annual</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-stone-700 mb-1">Payment Terms (months)</label>
                    <input type="number" name="payment_terms_months" value="{{ old('payment_terms_months', 60) }}" min="1" max="360" required class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">
                    @error('payment_terms_months')<p class="text-xs text-red-500 mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-stone-700 mb-1">Notes</label>
                <textarea name="notes" rows="3" class="w-full border border-stone-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-amber-400">{{ old('notes') }}</textarea>
            </div>
            <div class="flex gap-3 pt-2">
                <button type="submit" class="bg-amber-600 hover:bg-amber-700 text-white px-5 py-2 rounded-lg text-sm font-medium transition">Create Reservation</button>
                <a href="{{ route('broker.reservations.index') }}" class="px-5 py-2 rounded-lg text-sm font-medium border border-stone-200 text-stone-600 hover:bg-stone-50 transition">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection
