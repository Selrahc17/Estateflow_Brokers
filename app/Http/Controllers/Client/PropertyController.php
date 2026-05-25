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
            ->withCount(['lots' => fn($q) => $q->where('status', 'available')])
            ->latest()
            ->paginate(9);

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