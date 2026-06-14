<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertyController extends Controller
{
    public function index(): View
    {
        $properties = Property::with(['broker', 'lots'])
            ->when(request('status'), fn($q) => $q->where('status', request('status')))
            ->when(request('search'), fn($q) => $q->where('name', 'like', '%'.request('search').'%')->orWhere('address', 'like', '%'.request('search').'%'))
            ->latest()
            ->paginate(15);

        return view('pages.admin.properties.index', compact('properties'));
    }

    public function show(Property $property): View
    {
        $property->load(['broker', 'lots']);
        return view('pages.admin.properties.show', compact('property'));
    }

    public function updateStatus(Request $request, Property $property): RedirectResponse
    {
        $request->validate(['status' => 'required|in:available,hidden,flagged']);
        $property->update(['status' => $request->status]);

        return redirect()->route('admin.properties')->with('success', 'Property status updated.');
    }
}
