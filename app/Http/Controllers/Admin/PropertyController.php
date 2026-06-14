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

        // Find potential duplicates
        $potentialDuplicates = Property::select('name', 'city', 'province', 'latitude', 'longitude')
            ->selectRaw('COUNT(*) as count, GROUP_CONCAT(id) as ids')
            ->groupBy('name', 'city', 'province')
            ->havingRaw('COUNT(*) > 1')
            ->orWhere(function ($q) {
                $q->whereNotNull('latitude')->whereNotNull('longitude')
                  ->groupBy('latitude', 'longitude')
                  ->havingRaw('COUNT(*) > 1');
            })
            ->get();

        // Flatten potential duplicates into a list of property ID groups
        $duplicateGroups = [];
        foreach ($potentialDuplicates as $dup) {
            if ($dup->ids) {
                $ids = explode(',', $dup->ids);
                if (count($ids) > 1) {
                    $duplicateGroups[] = $ids;
                }
            }
        }

        return view('pages.admin.properties.index', compact('properties', 'duplicateGroups'));
    }

    public function show(Property $property): View
    {
        $property->load(['broker', 'lots']);
        // Find potential duplicates for this specific property
        $potentialDuplicates = Property::where('id', '!=', $property->id)
            ->where(function ($q) use ($property) {
                $q->where([
                    'name' => $property->name,
                    'city' => $property->city,
                    'province' => $property->province
                ]);
                if ($property->latitude && $property->longitude) {
                    $q->orWhere('latitude', $property->latitude)->where('longitude', $property->longitude);
                }
            })
            ->get();
        return view('pages.admin.properties.show', compact('property', 'potentialDuplicates'));
    }

    public function updateStatus(Request $request, Property $property): RedirectResponse
    {
        $request->validate(['status' => 'required|in:available,hidden,flagged']);
        $property->update(['status' => $request->status]);

        return redirect()->route('admin.properties')->with('success', 'Property status updated.');
    }

    public function deleteMultiple(Request $request): RedirectResponse
    {
        $request->validate(['ids' => 'required|array|min:2']);
        Property::whereIn('id', $request->ids)->delete();
        return back()->with('success', 'Selected duplicate properties deleted.');
    }
}
