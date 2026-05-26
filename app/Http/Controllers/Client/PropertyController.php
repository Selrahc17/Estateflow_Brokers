<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Lot;
use App\Models\Property;
use Illuminate\View\View;

class PropertyController extends Controller
{
    public function index(): View
    {
        $properties = Property::where('status', 'available')
            ->when(request('search'), fn($q) => $q->where('name', 'like', '%'.request('search').'%')->orWhere('description', 'like', '%'.request('search').'%'))
            ->when(request('location'), fn($q) => $q->where(fn($q2) => $q2->where('city', 'like', '%'.request('location').'%')->orWhere('province', 'like', '%'.request('location').'%')->orWhere('address', 'like', '%'.request('location').'%')))
            ->withCount(['lots' => fn($q) => $q->where('status', 'available')])
            ->latest()
            ->paginate(12);

        return view('pages.client.properties.index', compact('properties'));
    }

    public function show(string $slug): View
    {
        $property = Property::where('slug', $slug)
            ->with(['lots' => fn($q) => $q->where('status', 'available')])
            ->firstOrFail();

        return view('pages.client.properties.show', compact('property'));
    }
}