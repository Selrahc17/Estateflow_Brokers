{{--
    Optional selling details, shared by the create and edit forms.
    Expects $property on the edit form; $startNumber numbers the sections on the create form.
--}}
@use('App\Models\Property')
@php
    $property = $property ?? null;
    $startNumber = $startNumber ?? null;
    $input = 'w-full rounded-lg border border-stone-200 bg-white px-3 py-2.5 text-sm text-stone-800 placeholder:text-stone-400 focus:outline-none focus:ring-2 focus:ring-teal-400';
    $label = 'mb-1 block text-sm font-medium text-stone-700';
    $hint = 'mt-1 text-xs text-stone-500';
    $error = fn (string $name) => $errors->has($name) ? 'border-red-400' : '';
    $optional = '<span class="text-xs font-normal text-stone-500">(optional)</span>';

    $value = fn (string $field) => old($field, $property?->{$field});
    $checkedList = fn (string $field) => (array) old($field, $property?->{$field} ?? []);
    // Saved items that are not one of the tick boxes go back into the "Other" box.
    $otherText = fn (string $field, array $choices) => old("{$field}_other", implode(', ', array_diff($property?->{$field} ?? [], $choices)));

    $landmarks = array_values(old('landmarks', $property?->landmarks ?? []));
    $models = array_values(old('house_models', $property?->house_models ?? []));
    $modelErrors = collect($errors->getMessages())->filter(fn ($messages, $key) => str_starts_with($key, 'house_models'))->flatten()->unique();
    $landmarkErrors = collect($errors->getMessages())->filter(fn ($messages, $key) => str_starts_with($key, 'landmarks'))->flatten()->unique();

    $sections = [
        'terms' => ['Selling Stage and Payment Terms', 'Reservation, downpayment, discounts and fees. Fill in only what applies.'],
        'features' => ['Amenities and Highlights', 'Tick what the property has. Add anything else in "Other".'],
        'landmarks' => ['Nearby Landmarks', 'Places buyers know, with the travel time. For example: Tagaytay Rotonda, 15–20 mins.'],
        'models' => ['House Models', 'For projects that sell several models, like Type A and Type B, each with its own size and price.'],
    ];
    $number = fn (string $key) => $startNumber === null ? null : $startNumber + array_search($key, array_keys($sections), true);
@endphp

@foreach($sections as $key => [$title, $subtitle])
@php($sectionNumber = $number($key))
<section class="rounded-xl border border-stone-200 bg-white" aria-labelledby="section-{{ $key }}">
    <div class="flex items-start gap-3 border-b border-stone-100 px-5 py-4">
        @if($sectionNumber)
        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-teal-100 text-sm font-semibold text-teal-800" aria-hidden="true">{{ $sectionNumber }}</span>
        @endif
        <div>
            <h2 id="section-{{ $key }}" class="font-semibold text-stone-800">{{ $title }} {!! $optional !!}</h2>
            <p class="text-xs text-stone-500">{{ $subtitle }}</p>
        </div>
    </div>

    @if($key === 'terms')
    <div class="space-y-5 p-5" x-data="{ transferFee: @js($value('transfer_fee') ?? '') }">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label for="selling_stage" class="{{ $label }}">Selling Stage</label>
                <select id="selling_stage" name="selling_stage" class="{{ $input }} {{ $error('selling_stage') }}">
                    <option value="">Not specified</option>
                    @foreach(Property::SELLING_STAGES as $stage => $stageLabel)
                    <option value="{{ $stage }}" @selected($value('selling_stage') === $stage)>{{ $stageLabel }}</option>
                    @endforeach
                </select>
                @error('selling_stage')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="reservation_fee" class="{{ $label }}">Reservation Fee (₱)</label>
                <input id="reservation_fee" type="number" name="reservation_fee" step="0.01" min="0" value="{{ $value('reservation_fee') }}" placeholder="e.g. 10000" class="{{ $input }} {{ $error('reservation_fee') }}">
                @error('reservation_fee')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="downpayment_percent" class="{{ $label }}">Downpayment (%)</label>
                <input id="downpayment_percent" type="number" name="downpayment_percent" step="0.01" min="0" max="100" value="{{ $value('downpayment_percent') }}" placeholder="e.g. 25" class="{{ $input }} {{ $error('downpayment_percent') }}">
                @error('downpayment_percent')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <fieldset>
            <legend class="{{ $label }}">Payment Options</legend>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                @foreach(Property::PAYMENT_OPTIONS as $option => $optionLabel)
                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-stone-200 px-3 py-2 text-sm text-stone-700 hover:bg-stone-50">
                    <input type="checkbox" name="payment_options[]" value="{{ $option }}" @checked(in_array($option, $checkedList('payment_options'), true)) class="rounded border-stone-300 text-teal-600 focus:ring-teal-400">
                    {{ $optionLabel }}
                </label>
                @endforeach
            </div>
            @error('payment_options.*')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </fieldset>

        <div>
            <label for="installment_terms" class="{{ $label }}">Installment Terms</label>
            <input id="installment_terms" type="text" name="installment_terms" maxlength="255" value="{{ $value('installment_terms') }}" placeholder="e.g. Balance payable in up to 60 months, 0% interest" class="{{ $input }} {{ $error('installment_terms') }}">
            @error('installment_terms')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <p class="{{ $label }}">Full Cash Discount</p>
                <div class="flex gap-2">
                    <label for="cash_discount_type" class="sr-only">Discount type</label>
                    <select id="cash_discount_type" name="cash_discount_type" class="{{ $input }} w-36 shrink-0 {{ $error('cash_discount_type') }}">
                        <option value="">No discount</option>
                        <option value="percent" @selected($value('cash_discount_type') === 'percent')>Percent (%)</option>
                        <option value="amount" @selected($value('cash_discount_type') === 'amount')>Amount (₱)</option>
                    </select>
                    <label for="cash_discount" class="sr-only">Discount</label>
                    <input id="cash_discount" type="number" name="cash_discount" step="0.01" min="0" value="{{ $value('cash_discount') }}" placeholder="e.g. 10 or 100000" class="{{ $input }} {{ $error('cash_discount') }}">
                </div>
                <p class="{{ $hint }}">The property page shows the price after the discount.</p>
                @error('cash_discount_type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                @error('cash_discount')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <p class="{{ $label }}">Transfer Fee</p>
                <div class="flex gap-2">
                    <label for="transfer_fee" class="sr-only">Transfer fee</label>
                    <select id="transfer_fee" name="transfer_fee" x-model="transferFee" class="{{ $input }} {{ $error('transfer_fee') }}">
                        <option value="">Not specified</option>
                        @foreach(Property::TRANSFER_FEE_OPTIONS as $option => $optionLabel)
                        <option value="{{ $option }}" @selected($value('transfer_fee') === $option)>{{ $optionLabel }}</option>
                        @endforeach
                    </select>
                    <div x-show="transferFee === 'excluded'" x-cloak class="w-28 shrink-0">
                        <label for="transfer_fee_percent" class="sr-only">Transfer fee percent</label>
                        <input id="transfer_fee_percent" type="number" name="transfer_fee_percent" step="0.01" min="0" max="100" value="{{ $value('transfer_fee_percent') }}" placeholder="%" class="{{ $input }} {{ $error('transfer_fee_percent') }}">
                    </div>
                </div>
                <p class="{{ $hint }}" x-show="transferFee === 'excluded'" x-cloak>Add the percent if you know it, for example 8.</p>
                @error('transfer_fee')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                @error('transfer_fee_percent')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label for="buyer_requirements" class="{{ $label }}">Buyer Requirements</label>
            <textarea id="buyer_requirements" name="buyer_requirements" rows="2" maxlength="2000" placeholder="e.g. 2 valid IDs" class="{{ $input }} {{ $error('buyer_requirements') }}">{{ $value('buyer_requirements') }}</textarea>
            @error('buyer_requirements')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>
    </div>
    @endif

    @if($key === 'features')
    <div class="space-y-5 p-5">
        @foreach(['amenities' => ['Amenities', Property::AMENITY_CHOICES, 'e.g. Pavilion, Pet park'], 'selling_points' => ['Highlights', Property::SELLING_POINT_CHOICES, 'e.g. Near Tagaytay, 5 mins to the highway']] as $field => [$groupLabel, $choices, $otherPlaceholder])
        <fieldset>
            <legend class="{{ $label }}">{{ $groupLabel }}</legend>
            <div class="grid grid-cols-1 gap-2 sm:grid-cols-3">
                @foreach($choices as $choice)
                <label class="flex cursor-pointer items-center gap-2 rounded-lg border border-stone-200 px-3 py-2 text-sm text-stone-700 hover:bg-stone-50">
                    <input type="checkbox" name="{{ $field }}[]" value="{{ $choice }}" @checked(in_array($choice, $checkedList($field), true)) class="rounded border-stone-300 text-teal-600 focus:ring-teal-400">
                    {{ $choice }}
                </label>
                @endforeach
            </div>
            <label for="{{ $field }}_other" class="mt-3 block text-xs font-medium text-stone-600">Other {{ strtolower($groupLabel) }}, separated by commas</label>
            <input id="{{ $field }}_other" type="text" name="{{ $field }}_other" maxlength="500" value="{{ $otherText($field, $choices) }}" placeholder="{{ $otherPlaceholder }}" class="{{ $input }} mt-1 {{ $error("{$field}_other") }}">
            @error("{$field}_other")<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </fieldset>
        @endforeach
    </div>
    @endif

    @if($key === 'landmarks')
    <div class="space-y-3 p-5" x-data="{ rows: @js($landmarks ?: [['name' => '', 'travel_time' => '']]), max: {{ Property::MAX_LANDMARKS }} }">
        @foreach($landmarkErrors as $message)<p class="text-xs text-red-600">{{ $message }}</p>@endforeach
        <template x-for="(row, i) in rows" :key="i">
            <div class="flex items-start gap-2">
                <div class="flex-1">
                    <label :for="'landmark-name-' + i" class="sr-only">Place</label>
                    <input :id="'landmark-name-' + i" type="text" :name="`landmarks[${i}][name]`" x-model="row.name" maxlength="120" placeholder="Place, e.g. Tagaytay Rotonda" class="{{ $input }}">
                </div>
                <div class="w-32 shrink-0 sm:w-40">
                    <label :for="'landmark-time-' + i" class="sr-only">Travel time</label>
                    <input :id="'landmark-time-' + i" type="text" :name="`landmarks[${i}][travel_time]`" x-model="row.travel_time" maxlength="60" placeholder="15–20 mins" class="{{ $input }}">
                </div>
                <button type="button" @click="rows.splice(i, 1)" class="shrink-0 rounded-lg border border-stone-200 px-3 py-2.5 text-sm text-stone-500 hover:bg-red-50 hover:text-red-700" :aria-label="'Remove landmark ' + (i + 1)">&times;</button>
            </div>
        </template>
        <button type="button" x-show="rows.length < max" @click="rows.push({ name: '', travel_time: '' })" class="rounded-lg border border-dashed border-teal-300 px-3 py-2 text-sm font-medium text-teal-800 hover:bg-teal-50">+ Add landmark</button>
    </div>
    @endif

    @if($key === 'models')
    @php($blankModel = ['name' => '', 'position' => '', 'lot_area' => '', 'floor_area' => '', 'bedrooms' => '', 'bathrooms' => '', 'parking_slots' => '', 'price' => '', 'features' => ''])
    <div class="space-y-4 p-5" x-data="{ models: @js(array_map(fn ($model) => array_merge($blankModel, array_map(fn ($v) => $v ?? '', (array) $model)), $models)), blank: @js($blankModel), max: {{ Property::MAX_HOUSE_MODELS }} }">
        @foreach($modelErrors as $message)<p class="text-xs text-red-600">{{ $message }}</p>@endforeach
        <p x-show="! models.length" class="text-sm text-stone-500">No house models. The specifications above describe the whole listing.</p>
        <template x-for="(model, i) in models" :key="i">
            <div class="rounded-xl border border-stone-200 bg-stone-50 p-4">
                <div class="mb-3 flex items-center justify-between gap-3">
                    <p class="text-sm font-semibold text-stone-700" x-text="model.name || 'House model ' + (i + 1)"></p>
                    <button type="button" @click="models.splice(i, 1)" class="text-xs font-medium text-red-700 hover:underline">Remove</button>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                    <div class="col-span-2">
                        <label :for="'model-name-' + i" class="mb-1 block text-xs font-medium text-stone-600">Model name *</label>
                        <input :id="'model-name-' + i" type="text" :name="`house_models[${i}][name]`" x-model="model.name" maxlength="100" placeholder="e.g. Type A" class="{{ $input }}">
                    </div>
                    <div class="col-span-2">
                        <label :for="'model-position-' + i" class="mb-1 block text-xs font-medium text-stone-600">Unit position</label>
                        <select :id="'model-position-' + i" :name="`house_models[${i}][position]`" x-model="model.position" class="{{ $input }}">
                            <option value="">Not specified</option>
                            @foreach(Property::UNIT_POSITIONS as $position)
                            <option value="{{ $position }}">{{ $position }}</option>
                            @endforeach
                        </select>
                    </div>
                    @foreach(['lot_area' => ['Lot area (sqm)', '0.01'], 'floor_area' => ['Floor area (sqm)', '0.01'], 'bedrooms' => ['Bedrooms', '1'], 'bathrooms' => ['Toilet & bath', '1'], 'parking_slots' => ['Car garage', '1'], 'price' => ['Total contract price (₱)', '0.01']] as $field => [$fieldLabel, $step])
                    <div @class(['col-span-2' => $field === 'price'])>
                        <label :for="'model-{{ $field }}-' + i" class="mb-1 block text-xs font-medium text-stone-600">{{ $fieldLabel }}</label>
                        <input :id="'model-{{ $field }}-' + i" type="number" step="{{ $step }}" min="0" :name="`house_models[${i}][{{ $field }}]`" x-model="model.{{ $field }}" placeholder="—" class="{{ $input }}">
                    </div>
                    @endforeach
                    <div class="col-span-2 sm:col-span-4">
                        <label :for="'model-features-' + i" class="mb-1 block text-xs font-medium text-stone-600">Inclusions</label>
                        <input :id="'model-features-' + i" type="text" :name="`house_models[${i}][features]`" x-model="model.features" maxlength="255" placeholder="e.g. Living, dining, kitchen and service area" class="{{ $input }}">
                    </div>
                </div>
            </div>
        </template>
        <button type="button" x-show="models.length < max" @click="models.push({ ...blank })" class="rounded-lg border border-dashed border-teal-300 px-3 py-2 text-sm font-medium text-teal-800 hover:bg-teal-50">+ Add house model</button>
    </div>
    @endif
</section>
@endforeach
