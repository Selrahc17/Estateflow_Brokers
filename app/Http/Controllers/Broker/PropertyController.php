<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
            'type'        => 'required|in:House and Lot,Condominium,Townhouse,Lot Only,Office Space,Warehouse,Farm,Villa,Apartment',
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
            'price'       => 'nullable|numeric|min:0',
            'status'      => 'required|in:available,sold,coming_soon',
            'bedrooms'    => 'nullable|integer|min:0',
            'bathrooms'   => 'nullable|integer|min:0',
            'floor_area'  => 'nullable|numeric|min:0',
            'lot_area'    => 'nullable|numeric|min:0',
            'frontage'    => 'nullable|numeric|min:0',
            'stories'     => 'nullable|integer|min:0',
            'parking_slots' => 'nullable|integer|min:0',
            'amenities'   => 'nullable|array',
            'confirm_duplicate' => 'nullable|boolean',
        ]);

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
            $data['featured_image'] = 'https://yungpjrhvpjneanvyxnt.supabase.co/storage/v1/object/public/properties/' . $filename;
        }

        if ($request->hasFile('images')) {
            $request->validate(['images.*' => 'image|max:4096']);
            $urls = [];
            foreach ($request->file('images') as $img) {
                $filename = uniqid() . '.' . $img->getClientOriginalExtension();
                \Storage::disk('supabase')->put($filename, file_get_contents($img->getRealPath()), 'public');
                $urls[] = 'https://yungpjrhvpjneanvyxnt.supabase.co/storage/v1/object/public/properties/' . $filename;
            }
            $data['images'] = $urls;
        }

        Property::create($data);

        return redirect()->route('broker.properties.index')->with('success', 'Property created successfully.');
    }

    public function show(Property $property): View
    {
        $property->load(['lots' => fn($q) => $q->withCount('reservations')]);
        return view('pages.properties.show', compact('property'));
    }

    public function edit(Property $property): View
    {
        return view('pages.properties.edit', compact('property'));
    }

    public function update(Request $request, Property $property): RedirectResponse
    {
        $data = $request->validate([
            'name'        => 'required|string|max:255',
            'description' => 'nullable|string',
            'address'     => 'nullable|string',
            'city'        => 'nullable|string|max:255',
            'province'    => 'nullable|string|max:255',
            'type'        => 'required|in:House and Lot,Condominium,Townhouse,Lot Only,Office Space,Warehouse,Farm,Villa,Apartment',
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
            'price'       => 'nullable|numeric|min:0',
            'status'      => 'required|in:available,sold,coming_soon',
            'bedrooms'    => 'nullable|integer|min:0',
            'bathrooms'   => 'nullable|integer|min:0',
            'floor_area'  => 'nullable|numeric|min:0',
            'lot_area'    => 'nullable|numeric|min:0',
            'frontage'    => 'nullable|numeric|min:0',
            'stories'     => 'nullable|integer|min:0',
            'parking_slots' => 'nullable|integer|min:0',
            'amenities'   => 'nullable|array',
        ]);

        if ($request->hasFile('featured_image')) {
            $request->validate(['featured_image' => 'image|max:4096']);
            $file = $request->file('featured_image');
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            \Storage::disk('supabase')->put($filename, file_get_contents($file->getRealPath()), 'public');
            $data['featured_image'] = 'https://yungpjrhvpjneanvyxnt.supabase.co/storage/v1/object/public/properties/' . $filename;
        }

        if ($request->hasFile('images')) {
            $request->validate(['images.*' => 'image|max:4096']);
            $urls = [];
            foreach ($request->file('images') as $img) {
                $filename = uniqid() . '.' . $img->getClientOriginalExtension();
                \Storage::disk('supabase')->put($filename, file_get_contents($img->getRealPath()), 'public');
                $urls[] = 'https://yungpjrhvpjneanvyxnt.supabase.co/storage/v1/object/public/properties/' . $filename;
            }
            $data['images'] = $urls;
        }

        $property->update($data);

        return redirect()->route('broker.properties.index')->with('success', 'Property updated successfully.');
    }

    public function destroy(Property $property): RedirectResponse
    {
        $property->delete();
        return redirect()->route('broker.properties.index')->with('success', 'Property deleted successfully.');
    }
}