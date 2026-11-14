{{--
    The optional selling details on a property page. Every block hides itself when the
    agent left it empty. $card sets the card style of the page it sits on.
--}}
@php
    $card = $card ?? 'bg-white rounded-2xl border border-stone-200 p-6';
    $peso = fn ($amount) => '₱' . number_format((float) $amount, 0);
    $models = $property->house_models ?? [];
    $amenities = $property->amenities ?? [];
    $highlights = $property->selling_points ?? [];
    $landmarks = $property->landmarks ?? [];
    $price = (float) $property->price > 0 ? (float) $property->price : null;
@endphp

@if($property->hasPaymentTerms() || $property->selling_stage_label)
<div class="{{ $card }}" data-section="payment-terms">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-2">
        <h3 class="font-semibold text-stone-800">Price and Payment Terms</h3>
        @if($property->selling_stage_label)
        <span class="rounded-full bg-amber-100 px-3 py-1 text-xs font-semibold text-amber-800">{{ $property->selling_stage_label }}</span>
        @endif
    </div>

    <dl class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        @if($price)
        <div class="rounded-xl bg-stone-50 p-3">
            <dt class="text-xs text-stone-500">Total Contract Price</dt>
            <dd class="font-semibold text-stone-800">{{ $peso($price) }}</dd>
        </div>
        @endif
        @if((float) $property->reservation_fee > 0)
        <div class="rounded-xl bg-stone-50 p-3">
            <dt class="text-xs text-stone-500">Reservation Fee</dt>
            <dd class="font-semibold text-stone-800">{{ $peso($property->reservation_fee) }}</dd>
        </div>
        @endif
        @if($property->downpayment_percent_label)
        <div class="rounded-xl bg-stone-50 p-3">
            <dt class="text-xs text-stone-500">Downpayment</dt>
            <dd class="font-semibold text-stone-800">
                {{ $property->downpayment_percent_label }}
                @if($property->downpaymentAmount())<span class="text-sm font-normal text-stone-500">({{ $peso($property->downpaymentAmount()) }})</span>@endif
            </dd>
        </div>
        @endif
        @if($property->hasCashDiscount())
        <div class="rounded-xl border border-teal-200 bg-teal-50 p-3">
            <dt class="text-xs text-teal-800">Full Cash Discount</dt>
            <dd class="font-semibold text-teal-900">
                Less {{ $property->cash_discount_label }}
                @if($property->cashPrice())
                <span class="block text-sm font-normal">Net price: <span class="font-semibold">{{ $peso($property->cashPrice()) }}</span></span>
                @endif
            </dd>
        </div>
        @endif
    </dl>

    @if($property->payment_option_labels)
    <div class="mt-4">
        <p class="mb-2 text-xs font-medium text-stone-500">Payment Options</p>
        <ul class="flex flex-wrap gap-2">
            @foreach($property->payment_option_labels as $option)
            <li class="rounded-full border border-teal-200 bg-white px-3 py-1 text-xs font-medium text-teal-800">{{ $option }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    @if($property->installment_terms)
    <p class="mt-4 text-sm text-stone-600"><span class="font-medium text-stone-700">Installment:</span> {{ $property->installment_terms }}</p>
    @endif

    @if($property->transfer_fee_label)
    <p class="mt-3 flex items-start gap-2 text-sm text-stone-600">
        <svg class="mt-0.5 h-4 w-4 shrink-0 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
        {{ $property->transfer_fee_label }}.
    </p>
    @endif

    @if($property->buyer_requirements)
    <div class="mt-4 border-t border-stone-100 pt-4">
        <p class="mb-1 text-xs font-medium text-stone-500">Requirements</p>
        <p class="whitespace-pre-line text-sm text-stone-700">{{ $property->buyer_requirements }}</p>
    </div>
    @endif
</div>
@endif

@if($models)
<div class="{{ $card }}" data-section="house-models">
    <h3 class="mb-4 font-semibold text-stone-800">House Models</h3>
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
        @foreach($models as $model)
        @php
            $specs = array_filter([
                ! empty($model['lot_area']) ? (float) $model['lot_area'] . ' m² lot' : null,
                ! empty($model['floor_area']) ? (float) $model['floor_area'] . ' m² floor' : null,
                ! empty($model['bedrooms']) ? $model['bedrooms'] . ' bedroom' . ($model['bedrooms'] > 1 ? 's' : '') : null,
                ! empty($model['bathrooms']) ? $model['bathrooms'] . ' T&B' : null,
                ! empty($model['parking_slots']) ? $model['parking_slots'] . '-car garage' : null,
            ]);
            $modelPrice = (float) ($model['price'] ?? 0);
        @endphp
        <div class="rounded-xl border border-stone-200 p-4">
            <div class="flex items-start justify-between gap-2">
                <p class="font-semibold text-stone-800">{{ $model['name'] }}</p>
                @if(! empty($model['position']))
                <span class="shrink-0 rounded-full bg-stone-100 px-2 py-0.5 text-xs text-stone-600">{{ $model['position'] }}</span>
                @endif
            </div>
            @if($specs)
            <p class="mt-1 text-sm text-stone-600">{{ implode(' · ', $specs) }}</p>
            @endif
            @if(! empty($model['features']))
            <p class="mt-1 text-xs text-stone-500">{{ $model['features'] }}</p>
            @endif
            @if($modelPrice > 0)
            <p class="mt-3 text-lg font-bold text-teal-700">{{ $peso($modelPrice) }}</p>
            @if($property->cashPrice($modelPrice))
            <p class="text-xs text-teal-800">Full cash: {{ $peso($property->cashPrice($modelPrice)) }}</p>
            @endif
            @if($property->downpaymentAmount($modelPrice))
            <p class="text-xs text-stone-500">{{ $property->downpayment_percent_label }} downpayment: {{ $peso($property->downpaymentAmount($modelPrice)) }}</p>
            @endif
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

@if($amenities || $highlights)
<div class="{{ $card }}" data-section="amenities">
    <h3 class="mb-4 font-semibold text-stone-800">Amenities and Highlights</h3>
    @foreach(['Amenities' => $amenities, 'Highlights' => $highlights] as $heading => $items)
    @if($items)
    <div @class(['mt-4' => ! $loop->first && $amenities])>
        <p class="mb-2 text-xs font-medium text-stone-500">{{ $heading }}</p>
        <ul class="grid grid-cols-1 gap-2 sm:grid-cols-2">
            @foreach($items as $item)
            <li class="flex items-center gap-2 text-sm text-stone-700">
                <svg class="h-4 w-4 shrink-0 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ $item }}
            </li>
            @endforeach
        </ul>
    </div>
    @endif
    @endforeach
</div>
@endif

@if($landmarks)
<div class="{{ $card }}" data-section="landmarks">
    <h3 class="mb-4 font-semibold text-stone-800">Nearby Landmarks</h3>
    <ul class="divide-y divide-stone-100">
        @foreach($landmarks as $landmark)
        <li class="flex items-center justify-between gap-3 py-2 text-sm">
            <span class="text-stone-700">{{ $landmark['name'] }}</span>
            @if(! empty($landmark['travel_time']))
            <span class="shrink-0 text-stone-500">{{ $landmark['travel_time'] }}</span>
            @endif
        </li>
        @endforeach
    </ul>
</div>
@endif
