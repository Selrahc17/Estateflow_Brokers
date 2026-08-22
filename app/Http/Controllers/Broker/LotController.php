<?php

namespace App\Http\Controllers\Broker;

use App\Http\Controllers\Controller;
use App\Models\Lot;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class LotController extends Controller
{
    public function index(): View
    {
        $properties = Property::where('broker_id', auth()->id())->get();

        $lots = Lot::whereHas('property', fn($q) => $q->where('broker_id', auth()->id()))
            ->when(request('property_id'), fn($q) => $q->where('property_id', request('property_id')))
            ->when(request('status'), fn($q) => $q->where('status', request('status')))
            ->with('property')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.lots.index', compact('lots', 'properties'));
    }

    public function create(): View
    {
        $properties = Property::where('broker_id', auth()->id())->pluck('name', 'id');
        return view('pages.lots.create', compact('properties'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'property_id'  => ['required', Rule::exists('properties', 'id')->where('broker_id', auth()->id())],
            'lot_number'   => 'required|string|max:50',
            'price'        => 'nullable|numeric|min:0',
            'square_meters' => 'nullable|numeric|min:0',
            'status'       => 'required|in:available,reserved,sold',
            'description'  => 'nullable|string',
            'title'        => 'nullable|string|max:255',
        ]);

        Lot::create($data);

        return redirect()->route('broker.lots.index')->with('success', 'Lot created successfully.');
    }

    public function edit(Lot $lot): View
    {
        $this->ensureOwnership($lot);
        $properties = Property::where('broker_id', auth()->id())->pluck('name', 'id');
        return view('pages.lots.edit', compact('lot', 'properties'));
    }

    public function update(Request $request, Lot $lot): RedirectResponse
    {
        $this->ensureOwnership($lot);
        $data = $request->validate([
            'property_id'  => ['required', Rule::exists('properties', 'id')->where('broker_id', auth()->id())],
            'lot_number'   => 'required|string|max:50',
            'price'        => 'nullable|numeric|min:0',
            'square_meters' => 'nullable|numeric|min:0',
            'status'       => 'required|in:available,reserved,sold',
            'description'  => 'nullable|string',
            'title'        => 'nullable|string|max:255',
        ]);

        $lot->update($data);

        return redirect()->route('broker.lots.index')->with('success', 'Lot updated successfully.');
    }

    public function destroy(Lot $lot): RedirectResponse
    {
        $this->ensureOwnership($lot);
        $lot->delete();
        return redirect()->route('broker.lots.index')->with('success', 'Lot deleted successfully.');
    }

    private function ensureOwnership(Lot $lot): void
    {
        abort_unless($lot->property?->broker_id === auth()->id(), 403);
    }
}