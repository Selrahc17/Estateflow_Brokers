@extends('layouts.broker')

@section('title', $sale->exists ? 'Edit Sale' : 'Record Sale')
@section('page-title', $sale->exists ? 'Edit Sale' : 'Record Sale')
@section('page-subtitle', $sale->exists ? $sale->item_name : 'Add a sale to your commission records')

@section('content')
@php
    $editing = $sale->exists;
    $field = 'w-full rounded-lg border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-800 px-3 py-2.5 text-sm text-stone-800 dark:text-stone-100 focus:outline-none focus:ring-2 focus:ring-teal-400';
    $label = 'mb-1 block text-sm font-medium text-stone-700 dark:text-stone-300';
    $error = fn (string $name) => $errors->has($name) ? 'border-red-400' : '';
    $listings = $properties->map(fn ($property) => [
        'id' => $property->id,
        'name' => $property->name,
        'agent' => $property->broker_id,
        'price' => $property->price !== null ? (float) $property->price : null,
        'lots' => $property->lots->map(fn ($lot) => ['id' => $lot->id, 'name' => $lot->lot_number, 'price' => $lot->price !== null ? (float) $lot->price : null])->values(),
    ])->values();
@endphp

<div class="mx-auto max-w-3xl">
    <form method="POST" action="{{ $editing ? route('broker.commissions.update', $sale) : route('broker.commissions.store') }}"
          x-data="{
              listings: @js($listings),
              agent: @js((string) old('agent_id', $sale->agent_id)),
              property: @js((string) old('property_id', $sale->property_id)),
              lot: @js((string) old('lot_id', $sale->lot_id)),
              price: @js(old('selling_price', $sale->selling_price)),
              get listing() { return this.listings.find(l => String(l.id) === this.property) },
              get choices() { return this.agent ? this.listings.filter(l => String(l.agent) === this.agent) : this.listings },
              fillPrice() {
                  if (this.price !== null && this.price !== '') return;
                  const lot = this.listing?.lots.find(l => String(l.id) === this.lot);
                  this.price = lot?.price ?? this.listing?.price ?? null;
              },
          }"
          class="space-y-5 rounded-xl border border-stone-200 dark:border-stone-700 bg-white dark:bg-stone-900 p-6">
        @csrf
        @if($editing) @method('PUT') @endif

        @if($errors->any())
        <div role="alert" class="rounded-lg border border-red-200 dark:border-red-900 bg-red-50 dark:bg-red-950 px-4 py-3 text-sm text-red-700 dark:text-red-300">Please check the {{ $errors->count() === 1 ? 'field' : 'fields' }} marked below.</div>
        @endif

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="agent_id" class="{{ $label }}">Agent <span class="text-red-600" aria-hidden="true">*</span></label>
                <select id="agent_id" name="agent_id" x-model="agent" required class="{{ $field }} {{ $error('agent_id') }}">
                    <option value="">Select agent</option>
                    @foreach($agents as $agent)
                    <option value="{{ $agent->id }}">{{ $agent->name }}</option>
                    @endforeach
                </select>
                @error('agent_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="sold_at" class="{{ $label }}">Date of Sale <span class="text-red-600" aria-hidden="true">*</span></label>
                <input id="sold_at" type="date" name="sold_at" required max="{{ now()->toDateString() }}" value="{{ old('sold_at', $sale->sold_at?->toDateString()) }}" class="{{ $field }} {{ $error('sold_at') }}">
                @error('sold_at')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="property_id" class="{{ $label }}">Property / Project <span class="text-red-600" aria-hidden="true">*</span></label>
                <select id="property_id" name="property_id" x-model="property" @change="lot = ''; fillPrice()" required class="{{ $field }} {{ $error('property_id') }}">
                    <option value="">Select property</option>
                    <template x-for="item in choices" :key="item.id">
                        <option :value="String(item.id)" x-text="item.name" :selected="String(item.id) === property"></option>
                    </template>
                </select>
                <p class="mt-1 text-xs text-stone-500 dark:text-stone-400" x-show="agent && ! choices.length" x-cloak>This agent has no listings yet.</p>
                @error('property_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div x-show="listing?.lots.length" x-cloak>
                <label for="lot_id" class="{{ $label }}">Lot / Unit</label>
                <select id="lot_id" name="lot_id" x-model="lot" @change="fillPrice()" class="{{ $field }} {{ $error('lot_id') }}">
                    <option value="">Whole listing</option>
                    <template x-for="item in (listing?.lots ?? [])" :key="item.id">
                        <option :value="String(item.id)" x-text="item.name" :selected="String(item.id) === lot"></option>
                    </template>
                </select>
                @error('lot_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label for="selling_price" class="{{ $label }}">Selling Price (₱)</label>
                <input id="selling_price" type="number" step="0.01" min="0" name="selling_price" x-model="price" placeholder="0.00" class="{{ $field }} {{ $error('selling_price') }}">
                @error('selling_price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="agent_commission" class="{{ $label }}">Agent Commission (₱) <span class="text-xs font-normal text-stone-500">(optional)</span></label>
                <input id="agent_commission" type="number" step="0.01" min="0" name="agent_commission" value="{{ old('agent_commission', $sale->agent_commission) }}" placeholder="0.00" class="{{ $field }} {{ $error('agent_commission') }}">
                @error('agent_commission')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="broker_commission" class="{{ $label }}">Broker Commission (₱) <span class="text-xs font-normal text-stone-500">(optional)</span></label>
                <input id="broker_commission" type="number" step="0.01" min="0" name="broker_commission" value="{{ old('broker_commission', $sale->broker_commission) }}" placeholder="0.00" class="{{ $field }} {{ $error('broker_commission') }}">
                @error('broker_commission')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
        <p class="-mt-2 text-xs text-stone-500 dark:text-stone-400">Commission amounts are private. Agents never see them.</p>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label for="status" class="{{ $label }}">Commission Status <span class="text-red-600" aria-hidden="true">*</span></label>
                <select id="status" name="status" required class="{{ $field }} {{ $error('status') }}">
                    @foreach(\App\Models\Sale::STATUSES as $value => $text)
                    <option value="{{ $value }}" @selected(old('status', $sale->status) === $value)>{{ $text }}</option>
                    @endforeach
                </select>
                @error('status')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <label class="flex cursor-pointer items-start gap-3 rounded-lg border border-stone-200 dark:border-stone-700 p-3 sm:mt-6">
                <input type="checkbox" name="show_status_to_agent" value="1" @checked(old('show_status_to_agent', $sale->show_status_to_agent)) class="mt-0.5 rounded border-stone-300 text-teal-600 focus:ring-teal-400">
                <span>
                    <span class="block text-sm font-medium text-stone-700 dark:text-stone-200">Show status to agent</span>
                    <span class="block text-xs text-stone-500 dark:text-stone-400">The agent sees Pending, Transferred or Received for this sale. Amounts stay hidden.</span>
                </span>
            </label>
        </div>

        <div>
            <label for="notes" class="{{ $label }}">Notes <span class="text-xs font-normal text-stone-500">(optional, only you see these)</span></label>
            <textarea id="notes" name="notes" rows="2" maxlength="1000" class="{{ $field }} {{ $error('notes') }}">{{ old('notes', $sale->notes) }}</textarea>
            @error('notes')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="flex flex-col-reverse gap-3 border-t border-stone-100 dark:border-stone-800 pt-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                @if($editing)
                <button type="submit" form="delete-sale" class="text-sm font-medium text-red-700 dark:text-red-400 hover:underline">Remove from records</button>
                @endif
            </div>
            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('broker.commissions.index') }}" class="rounded-lg px-4 py-2.5 text-sm font-medium text-stone-600 dark:text-stone-300 hover:bg-stone-50 dark:hover:bg-stone-800">Cancel</a>
                <button type="submit" class="rounded-lg bg-[var(--color-primary)] px-6 py-2.5 text-sm font-semibold text-white hover:bg-[var(--color-primary-dark)]">{{ $editing ? 'Save Changes' : 'Record Sale' }}</button>
            </div>
        </div>
    </form>

    @if($editing)
    <form id="delete-sale" method="POST" action="{{ route('broker.commissions.destroy', $sale) }}" onsubmit="return confirm('Remove this sale from your commission records?')">
        @csrf @method('DELETE')
    </form>
    @endif
</div>
@endsection
