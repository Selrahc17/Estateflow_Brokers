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
            ->withCount('lots')
            ->latest()
            ->paginate(10);

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
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
            'price'       => 'nullable|numeric|min:0',
            'status'      => 'required|in:available,sold,coming_soon',
            'amenities'   => 'nullable|array',
        ]);

        $data['slug']      = Str::slug($data['name']) . '-' . uniqid();
        $data['broker_id'] = auth()->id();

        if ($request->hasFile('featured_image')) {
            $request->validate(['featured_image' => 'image|max:4096']);
            $data['featured_image'] = $request->file('featured_image')->store('properties', 'public');
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
            'latitude'    => 'nullable|numeric|between:-90,90',
            'longitude'   => 'nullable|numeric|between:-180,180',
            'price'       => 'nullable|numeric|min:0',
            'status'      => 'required|in:available,sold,coming_soon',
            'amenities'   => 'nullable|array',
        ]);

        if ($request->hasFile('featured_image')) {
            $request->validate(['featured_image' => 'image|max:4096']);
            // Delete old image
            if ($property->featured_image) {
                \Storage::disk('public')->delete($property->featured_image);
            }
            $data['featured_image'] = $request->file('featured_image')->store('properties', 'public');
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