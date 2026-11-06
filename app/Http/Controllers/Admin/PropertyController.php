<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
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
            ->when(request('search'), fn($q) => $q->where(function ($query) {
                $query->where('name', 'like', '%'.request('search').'%')
                    ->orWhere('address', 'like', '%'.request('search').'%');
            }))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $duplicateGroups = $this->duplicateGroups();

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
        $request->validate(['status' => 'required|in:available,coming_soon,sold,hidden,flagged']);

        $property->update(['status' => $request->status]);

        if (in_array($request->status, ['hidden', 'flagged'], true) && $property->broker_id) {
            AppNotification::create([
                'user_id' => $property->broker_id,
                'type' => 'property_moderated',
                'title' => 'Listing removed from the public site',
                'message' => "An admin marked {$property->name} as {$request->status}. Clients can no longer see it.",
                'link' => route('agent.properties.show', $property),
                'is_read' => false,
            ]);
        }

        return back()->with('success', 'Property status updated.');
    }

    public function deleteMultiple(Request $request): RedirectResponse
    {
        $request->validate(
            ['ids' => 'required|array|min:1', 'ids.*' => 'integer'],
            ['ids.required' => 'Select at least one property to delete.']
        );
        Property::whereIn('id', $request->ids)->delete();
        return back()->with('success', 'Selected duplicate properties deleted.');
    }

    /**
     * Listings with the same name and place, or the same map pin.
     *
     * @return array<int, \Illuminate\Support\Collection<int, Property>>
     */
    private function duplicateGroups(): array
    {
        $properties = Property::with('broker')->get();

        $byName = $properties->groupBy(fn (Property $property) => strtolower("{$property->name}|{$property->city}|{$property->province}"));
        $byPin = $properties->filter(fn (Property $property) => $property->latitude && $property->longitude)
            ->groupBy(fn (Property $property) => "{$property->latitude}|{$property->longitude}");

        return $byName->values()->concat($byPin->values())
            ->filter(fn ($group) => $group->count() > 1)
            ->unique(fn ($group) => $group->pluck('id')->sort()->implode(','))
            ->values()
            ->all();
    }
}
