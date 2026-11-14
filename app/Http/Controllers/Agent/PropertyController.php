<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Services\AIService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PropertyController extends Controller
{
    public function index(): View
    {
        $properties = Property::where('broker_id', auth()->id())
            ->when(request('search'), fn($q) => $q->where('name', 'like', '%'.request('search').'%'))
            ->when(request('status'), fn($q) => $q->where('status', request('status')))
            ->when(request('type'), fn($q) => $q->where('type', request('type')))
            ->withCount('lots')
            ->withCount(['lots as available_lots_count' => fn($q) => $q->where('status', 'available')])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.properties.index', compact('properties'));
    }

    public function commission(): View
    {
        $properties = Property::where('broker_id', auth()->id())
            ->latest()
            ->paginate(12);

        return view('pages.commission.index', compact('properties'));
    }

    public function create(): View
    {
        return view('pages.properties.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'address'     => 'nullable|string',
            'city'        => 'nullable|string|max:255',
            'province'    => 'nullable|string|max:255',
            'type'        => ['required', Rule::in(Property::TYPES)],
            'developer'   => 'nullable|string|max:255',
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
            'price'       => 'nullable|numeric|min:0',
            'agent_commission' => 'nullable|numeric|min:0|max:100',
            'valid_until' => 'nullable|date',
            'status'      => 'required|in:available,sold,coming_soon',
            'bedrooms'    => 'nullable|integer|min:0',
            'bathrooms'   => 'nullable|integer|min:0',
            'floor_area'  => 'nullable|numeric|min:0',
            'lot_area'    => 'nullable|numeric|min:0',
            'frontage'    => 'nullable|numeric|min:0',
            'stories'     => 'nullable|integer|min:0',
            'parking_slots' => 'nullable|integer|min:0',
            'confirm_duplicate' => 'nullable|boolean',
        ]);
        $data = [...$data, ...$this->sellingDetails($request)];

        // Check for potential duplicates
        $existingDuplicate = Property::where('broker_id', '!=', auth()->id())
            ->where(function ($q) use ($data) {
                $q->where('name', $data['name'])
                  ->where('city', $data['city'] ?? '')
                  ->where('province', $data['province'] ?? '');
                if (!empty($data['latitude']) && !empty($data['longitude'])) {
                    $q->orWhere('latitude', $data['latitude'])->where('longitude', $data['longitude']);
                }
            })->first();

        if ($existingDuplicate && !$request->has('confirm_duplicate')) {
            return back()->withErrors([
                'duplicate_warning' => 'A similar property already exists in the system. Please review or confirm to proceed.',
            ])->withInput()->with('duplicate_property', $existingDuplicate);
        }

        $data['slug']      = Str::slug($data['name']) . '-' . uniqid();
        $data['broker_id'] = auth()->id();

        if ($request->hasFile('featured_image')) {
            $request->validate(['featured_image' => 'image|max:4096']);
            $file = $request->file('featured_image');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            \Storage::disk('supabase')->put($filename, file_get_contents($file->getRealPath()), 'public');
            $data['featured_image'] = config('filesystems.disks.supabase.endpoint')
                ? rtrim(str_replace('/storage/v1/s3', '', config('filesystems.disks.supabase.endpoint')), '/') . '/storage/v1/object/public/' . config('filesystems.disks.supabase.bucket') . '/' . $filename
                : null;
        }

        $data['images'] = $this->storeGalleryImages($request);
        $data['developer_logo'] = $this->storeDeveloperLogo($request);

        Property::create($data);

        return redirect()->route('agent.properties.index')->with('success', 'Property created successfully.');
    }

    public function show(Property $property): View
    {
        $this->ensureOwnership($property);
        $property->load(['lots' => fn($q) => $q->withCount('reservations')]);
        return view('pages.properties.show', compact('property'));
    }

    public function edit(Property $property): View
    {
        $this->ensureOwnership($property);
        return view('pages.properties.edit', compact('property'));
    }

    public function update(Request $request, Property $property): RedirectResponse
    {
        $this->ensureOwnership($property);
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'address'     => 'nullable|string',
            'city'        => 'nullable|string|max:255',
            'province'    => 'nullable|string|max:255',
            'type'        => ['required', Rule::in(Property::TYPES)],
            'developer'   => 'nullable|string|max:255',
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
            'price'       => 'nullable|numeric|min:0',
            'agent_commission' => 'nullable|numeric|min:0|max:100',
            'valid_until' => 'nullable|date',
            'status'      => 'required|in:available,sold,coming_soon',
            'bedrooms'    => 'nullable|integer|min:0',
            'bathrooms'   => 'nullable|integer|min:0',
            'floor_area'  => 'nullable|numeric|min:0',
            'lot_area'    => 'nullable|numeric|min:0',
            'frontage'    => 'nullable|numeric|min:0',
            'stories'     => 'nullable|integer|min:0',
            'parking_slots' => 'nullable|integer|min:0',
        ]);
        $data = [...$data, ...$this->sellingDetails($request)];

        if ($request->hasFile('featured_image')) {
            $request->validate(['featured_image' => 'image|max:4096']);
            $file = $request->file('featured_image');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            \Storage::disk('supabase')->put($filename, file_get_contents($file->getRealPath()), 'public');
            $baseUrl = rtrim(str_replace('/storage/v1/s3', '', config('filesystems.disks.supabase.endpoint')), '/');
            $data['featured_image'] = $baseUrl . '/storage/v1/object/public/' . config('filesystems.disks.supabase.bucket') . '/' . $filename;
        }

        $removedImages = $request->validate([
            'remove_images' => 'nullable|array',
            'remove_images.*' => 'string',
        ])['remove_images'] ?? [];

        $keptImages = array_values(array_diff($property->images ?? [], $removedImages));
        $data['images'] = [...$keptImages, ...$this->storeGalleryImages($request, count($keptImages))];

        if ($newLogo = $this->storeDeveloperLogo($request)) {
            $data['developer_logo'] = $newLogo;
        } elseif ($request->boolean('remove_developer_logo')) {
            $data['developer_logo'] = null;
        }

        // Only an admin can release a listing they hid or flagged.
        if (in_array($property->status, ['hidden', 'flagged'], true)) {
            unset($data['status']);
        }

        $property->update($data);

        return redirect()->route('agent.properties.index')->with('success', 'Property updated successfully.');
    }

    public function destroy(Property $property): RedirectResponse
    {
        $this->ensureOwnership($property);
        $property->delete();
        return redirect()->route('agent.properties.index')->with('success', 'Property deleted successfully.');
    }

    /**
     * The optional selling details: payment terms, transfer fee, amenities, landmarks,
     * house models and requirements. Empty rows are dropped and empty lists saved as
     * null, so unticking everything on the edit form clears it.
     */
    private function sellingDetails(Request $request): array
    {
        $listOrNull = fn (?array $items) => $items ? array_values($items) : null;
        $text = 'nullable|string|max:255';

        $data = $request->validate([
            'selling_stage' => ['nullable', Rule::in(array_keys(Property::SELLING_STAGES))],
            'reservation_fee' => 'nullable|numeric|min:0|max:999999999',
            'downpayment_percent' => 'nullable|numeric|min:0|max:100',
            'payment_options' => 'nullable|array',
            'payment_options.*' => [Rule::in(array_keys(Property::PAYMENT_OPTIONS))],
            'installment_terms' => $text,
            'cash_discount_type' => ['nullable', 'required_with:cash_discount', 'in:percent,amount'],
            'cash_discount' => ['nullable', 'numeric', 'min:0', 'max:999999999', Rule::when($request->input('cash_discount_type') === 'percent', 'max:100')],
            'transfer_fee' => ['nullable', Rule::in(array_keys(Property::TRANSFER_FEE_OPTIONS))],
            'transfer_fee_percent' => 'nullable|numeric|min:0|max:100',
            'amenities' => 'nullable|array',
            'amenities.*' => 'string|max:100',
            'amenities_other' => 'nullable|string|max:500',
            'selling_points' => 'nullable|array',
            'selling_points.*' => 'string|max:100',
            'selling_points_other' => 'nullable|string|max:500',
            'landmarks' => 'nullable|array|max:' . Property::MAX_LANDMARKS,
            'landmarks.*.name' => 'nullable|string|max:120',
            'landmarks.*.travel_time' => 'nullable|string|max:60',
            'house_models' => 'nullable|array|max:' . Property::MAX_HOUSE_MODELS,
            'house_models.*.name' => ['nullable', 'string', 'max:100'],
            'house_models.*.position' => ['nullable', Rule::in(Property::UNIT_POSITIONS)],
            'house_models.*.lot_area' => 'nullable|numeric|min:0|max:99999999',
            'house_models.*.floor_area' => 'nullable|numeric|min:0|max:99999999',
            'house_models.*.bedrooms' => 'nullable|integer|min:0|max:100',
            'house_models.*.bathrooms' => 'nullable|integer|min:0|max:100',
            'house_models.*.parking_slots' => 'nullable|integer|min:0|max:100',
            'house_models.*.price' => 'nullable|numeric|min:0|max:999999999999',
            'house_models.*.features' => $text,
            'buyer_requirements' => 'nullable|string|max:2000',
        ], [
            'cash_discount.max' => 'A percentage discount cannot be more than 100%.',
            'cash_discount_type.required_with' => 'Choose whether the discount is a percentage or a peso amount.',
            'landmarks.max' => 'A listing can have up to ' . Property::MAX_LANDMARKS . ' landmarks.',
            'house_models.max' => 'A listing can have up to ' . Property::MAX_HOUSE_MODELS . ' house models.',
        ]);

        // Ticked choices plus anything typed in "Other", separated by commas.
        $withOther = function (string $field) use ($data): ?array {
            $typed = array_map('trim', explode(',', (string) ($data["{$field}_other"] ?? '')));
            $all = array_unique(array_filter([...($data[$field] ?? []), ...$typed], fn ($item) => $item !== ''));

            return $all ? array_values($all) : null;
        };

        $landmarks = collect($data['landmarks'] ?? [])
            ->map(fn ($row) => ['name' => trim((string) ($row['name'] ?? '')), 'travel_time' => trim((string) ($row['travel_time'] ?? ''))])
            ->filter(fn ($row) => $row['name'] !== '')
            ->all();

        $modelFields = ['name', 'position', 'lot_area', 'floor_area', 'bedrooms', 'bathrooms', 'parking_slots', 'price', 'features'];
        $models = collect($data['house_models'] ?? [])
            ->map(fn ($row) => collect($modelFields)->mapWithKeys(fn ($field) => [$field => is_string($row[$field] ?? null) ? trim($row[$field]) : ($row[$field] ?? null)])
                ->map(fn ($value) => $value === '' ? null : $value)->all())
            ->filter(fn ($row) => array_filter($row, fn ($value) => $value !== null));

        $unnamed = $models->filter(fn ($row) => $row['name'] === null)->keys()->first();
        if ($unnamed !== null) {
            throw ValidationException::withMessages(["house_models.{$unnamed}.name" => 'Give this house model a name, like "Type A".']);
        }

        $hasDiscount = (float) ($data['cash_discount'] ?? 0) > 0;

        return [
            'selling_stage' => $data['selling_stage'] ?? null,
            'reservation_fee' => $data['reservation_fee'] ?? null,
            'downpayment_percent' => $data['downpayment_percent'] ?? null,
            'payment_options' => $listOrNull(array_unique($data['payment_options'] ?? [])),
            'installment_terms' => $data['installment_terms'] ?? null,
            'cash_discount_type' => $hasDiscount ? $data['cash_discount_type'] : null,
            'cash_discount' => $hasDiscount ? $data['cash_discount'] : null,
            'transfer_fee' => $data['transfer_fee'] ?? null,
            'transfer_fee_percent' => ($data['transfer_fee'] ?? null) === 'excluded' ? ($data['transfer_fee_percent'] ?? null) : null,
            'amenities' => $withOther('amenities'),
            'selling_points' => $withOther('selling_points'),
            'landmarks' => $listOrNull($landmarks),
            'house_models' => $listOrNull($models->all()),
            'buyer_requirements' => filled($data['buyer_requirements'] ?? null) ? trim($data['buyer_requirements']) : null,
        ];
    }

    private function ensureOwnership(Property $property): void
    {
        abort_unless((int) $property->broker_id === (int) auth()->id(), 403);
    }

    /**
     * Validate and upload the request's gallery photos, returning their public URLs.
     *
     * @return array<int, string>
     */
    /**
     * SVG is left out on purpose: it can carry scripts.
     */
    private function storeDeveloperLogo(Request $request): ?string
    {
        if (! $request->hasFile('developer_logo')) {
            return null;
        }

        $request->validate(['developer_logo' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:2048']]);

        $logo = $request->file('developer_logo');
        $filename = 'developer-logos/' . uniqid() . '.' . $logo->extension();
        \Storage::disk('supabase')->put($filename, file_get_contents($logo->getRealPath()), 'public');

        return rtrim(str_replace('/storage/v1/s3', '', config('filesystems.disks.supabase.endpoint')), '/') . '/storage/v1/object/public/' . config('filesystems.disks.supabase.bucket') . '/' . $filename;
    }

    private function storeGalleryImages(Request $request, int $existingCount = 0): array
    {
        if (! $request->hasFile('images')) {
            return [];
        }

        $request->validate([
            'images' => ['array', 'max:' . max(0, Property::MAX_GALLERY_IMAGES - $existingCount)],
            'images.*' => ['image', 'max:4096'],
        ], [
            'images.max' => 'A property can have up to ' . Property::MAX_GALLERY_IMAGES . ' additional images.',
        ]);

        $baseUrl = rtrim(str_replace('/storage/v1/s3', '', config('filesystems.disks.supabase.endpoint')), '/');

        return array_map(function ($image) use ($baseUrl) {
            $filename = uniqid() . '.' . $image->getClientOriginalExtension();
            \Storage::disk('supabase')->put($filename, file_get_contents($image->getRealPath()), 'public');

            return $baseUrl . '/storage/v1/object/public/' . config('filesystems.disks.supabase.bucket') . '/' . $filename;
        }, $request->file('images'));
    }

    public function aiDescribe(Request $request): JsonResponse
    {
        $specs = $request->validate([
            'name'          => 'nullable|string',
            'type'          => 'nullable|string',
            'city'          => 'nullable|string',
            'province'      => 'nullable|string',
            'bedrooms'      => 'nullable|integer',
            'bathrooms'     => 'nullable|integer',
            'floor_area'    => 'nullable|numeric',
            'lot_area'      => 'nullable|numeric',
            'price'         => 'nullable|numeric',
        ]);

        $ai = app(AIService::class);
        $description = $ai->generatePropertyDescription($specs);

        return response()->json(['description' => $description]);
    }
}