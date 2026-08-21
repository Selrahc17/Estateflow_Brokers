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
            ->when(request('city'), fn($q) => $q->where('city', request('city')))
            ->when(request('province'), fn($q) => $q->where('province', request('province')))
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

        $types = Property::where('status', 'available')->distinct()->orderBy('type')->pluck('type');
        $cities = Property::where('status', 'available')->whereNotNull('city')->where('city', '!=', '')->distinct()->orderBy('city')->pluck('city');
        $provinces = Property::where('status', 'available')->whereNotNull('province')->where('province', '!=', '')->distinct()->orderBy('province')->pluck('province');

        return view('pages.client.properties.index', compact('properties', 'types', 'cities', 'provinces'));
    }

    public function show(string $slug): View
    {
        $property = Property::where('slug', $slug)
            ->with(['lots' => fn($q) => $q->where('status', 'available'), 'broker'])
            ->firstOrFail();

        $property->increment('view_count');

        $relatedProperties = Property::where('status', 'available')
            ->where('id', '!=', $property->id)
            ->where(function ($query) use ($property) {
                $query->where('type', $property->type)
                    ->orWhere('city', $property->city)
                    ->orWhere('province', $property->province);
            })
            ->withCount(['lots' => fn($q) => $q->where('status', 'available')])
            ->latest()
            ->take(4)
            ->get();

        if ($relatedProperties->count() < 4) {
            $additionalProperties = Property::where('status', 'available')
                ->where('id', '!=', $property->id)
                ->whereNotIn('id', $relatedProperties->pluck('id'))
                ->withCount(['lots' => fn($q) => $q->where('status', 'available')])
                ->latest()
                ->take(4 - $relatedProperties->count())
                ->get();

            $relatedProperties = $relatedProperties->concat($additionalProperties);
        }

        return view('pages.client.properties.show', compact('property', 'relatedProperties'));
    }
}