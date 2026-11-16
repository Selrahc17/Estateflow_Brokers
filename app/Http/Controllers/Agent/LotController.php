<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use App\Models\Lot;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class LotController extends Controller
{
    /**
     * Lots of one project at a time, as a site-plan grid grouped by block or as
     * a list. Lot numbers sort naturally, so Block 2 comes before Block 15.
     */
    public function index(Request $request): View
    {
        $properties = Property::where('broker_id', auth()->id())
            ->withCount([
                'lots',
                'lots as available_count' => fn ($query) => $query->where('status', 'available'),
                'lots as reserved_count' => fn ($query) => $query->where('status', 'reserved'),
                'lots as sold_count' => fn ($query) => $query->where('status', 'sold'),
            ])
            ->orderByDesc('lots_count')->orderBy('name')
            ->get();

        // Opens on the first project with lots, unless one was chosen.
        $selected = $properties->firstWhere('id', $request->integer('property_id'))
            ?? ($request->has('property_id') ? null : $properties->firstWhere('lots_count', '>', 0));
        $view = $request->input('view') === 'list' ? 'list' : 'grid';
        $status = array_key_exists((string) $request->input('status'), Lot::STATUSES) ? $request->input('status') : null;
        $search = trim((string) $request->input('q'));

        $all = Lot::whereIn('property_id', $selected ? [$selected->id] : $properties->pluck('id'))
            ->with('property:id,name')
            ->get()
            ->sort(fn (Lot $a, Lot $b) => [$a->property_id === $selected?->id ? 0 : $a->property?->name, (int) ($a->block ?? PHP_INT_MAX), $a->block, (int) $a->short_number, $a->lot_number]
                <=> [$b->property_id === $selected?->id ? 0 : $b->property?->name, (int) ($b->block ?? PHP_INT_MAX), $b->block, (int) $b->short_number, $b->lot_number])
            ->values();

        $stats = [
            'total' => $all->count(),
            'available' => $all->where('status', 'available')->count(),
            'reserved' => $all->where('status', 'reserved')->count(),
            'sold' => $all->where('status', 'sold')->count(),
            'available_value' => $all->where('status', 'available')->sum(fn (Lot $lot) => (float) $lot->price),
        ];

        $lots = $all
            ->when($status, fn ($lots) => $lots->where('status', $status))
            ->when($search !== '', fn ($lots) => $lots->filter(fn (Lot $lot) => str_contains(mb_strtolower($lot->lot_number), mb_strtolower($search))))
            ->values();

        return view('pages.lots.index', compact('properties', 'selected', 'view', 'status', 'search', 'stats', 'lots'));
    }

    /** One-tap status change. Sold adds the lot to the broker's commission records. */
    public function updateStatus(Request $request, Lot $lot): RedirectResponse
    {
        $this->ensureOwnership($lot);
        $status = $request->validate(['status' => ['required', Rule::in(array_keys(Lot::STATUSES))]])['status'];

        $lot->update(['status' => $status]);

        return back()->with('success', $status === 'sold'
            ? "{$lot->lot_number} is marked as sold and was added to your broker's commission records."
            : "{$lot->lot_number} is now " . strtolower(Lot::STATUSES[$status]) . '.');
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

        return redirect()->route('agent.lots.index')->with('success', 'Lot created successfully.');
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

        return redirect()->route('agent.lots.index')->with('success', 'Lot updated successfully.');
    }

    public function destroy(Lot $lot): RedirectResponse
    {
        $this->ensureOwnership($lot);
        $lot->delete();
        return redirect()->route('agent.lots.index')->with('success', 'Lot deleted successfully.');
    }

    /** Marks one lot as sold, which adds it to the broker's commission table. */
    public function markSold(Lot $lot): RedirectResponse
    {
        $this->ensureOwnership($lot);

        $lot->update(['status' => 'sold']);

        return back()->with('success', "{$lot->lot_number} is marked as sold and was added to your broker's commission records.");
    }

    private function ensureOwnership(Lot $lot): void
    {
        abort_unless($lot->property?->broker_id === auth()->id(), 403);
    }
}