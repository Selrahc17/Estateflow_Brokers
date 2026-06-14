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
            ->when(request('type'), fn($q) => $q->where('type', request('type')))
            ->when(request('location'), fn($q) => $q->where(fn($q2) => $q2->where('city', 'like', '%'.request('location').'%')->orWhere('province', 'like', '%'.request('location').'%')->orWhere('address', 'like', '%'.request('location').'%')))
            ->when(request('min_price'), fn($q) => $q->where('price', '>=', request('min_price')))
            ->when(request('max_price'), fn($q) => $q->where('price', '<=', request('max_price')))
            ->withCount(['lots' => fn($q) => $q->where('status', 'available')])
            ->when(request('sort'), function($q) {
                return match(request('sort')) {
                    'price_asc' => $q->orderBy('price', 'asc'),
                    'price_desc' => $q->orderBy('price', 'desc'),
                    'newest' => $q->latest(),
                    'oldest' => $q->oldest(),
                    default => $q->latest(),
                };
            }, fn($q) => $q->latest())
            ->paginate(12);

        $types = Property::distinct()->pluck('type');

        return view('pages.client.properties.index', compact('properties', 'types'));
    }

    public function show(string $slug): View
    {
        $property = Property::where('slug', $slug)
            ->with(['lots' => fn($q) => $q->where('status', 'available'), 'broker'])
            ->firstOrFail();

        $property->increment('view_count');

        return view('pages.client.properties.show', compact('property'));
    }
}