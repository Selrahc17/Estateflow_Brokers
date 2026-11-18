<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\PropertyView;
use App\Services\AIService;
use App\Services\PropertyComparison;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

/**
 * Side-by-side comparison of up to four listings. The picks live in the
 * session, so guests can compare too.
 */
class CompareController extends Controller
{
    private const SESSION_KEY = 'compare';

    public function __construct(private PropertyComparison $comparison) {}

    public function index(Request $request): View
    {
        $properties = $this->selected($request);
        $suggestions = $this->suggestions($request, $properties);

        return view('pages.client.compare.index', [
            'properties' => $properties,
            'sections' => $properties->count() >= 2 ? $this->comparison->sections($properties) : [],
            'suggestions' => $suggestions,
            'max' => PropertyComparison::MAX,
        ]);
    }

    public function toggle(Request $request, Property $property): RedirectResponse
    {
        abort_if(in_array($property->status, ['hidden', 'flagged'], true), 404);

        $ids = collect($request->session()->get(self::SESSION_KEY, []));

        if ($ids->contains($property->id)) {
            $request->session()->put(self::SESSION_KEY, $ids->reject(fn ($id) => $id === $property->id)->values()->all());

            return back()->with('success', "{$property->name} was removed from your comparison.");
        }

        if ($ids->count() >= PropertyComparison::MAX) {
            return back()->with('error', 'You can compare up to '.PropertyComparison::MAX.' properties. Remove one first.');
        }

        $ids->push($property->id);
        $request->session()->put(self::SESSION_KEY, $ids->values()->all());
        $count = $ids->count();

        return back()->with('success', $count >= 2
            ? "{$property->name} added. You are comparing {$count} properties."
            : "{$property->name} added. Add one more property to compare.");
    }

    public function clear(Request $request): RedirectResponse
    {
        $request->session()->forget([self::SESSION_KEY, 'compare_summary']);

        return redirect()->route('client.compare')->with('success', 'Your comparison was cleared.');
    }

    public function summary(Request $request, AIService $ai): RedirectResponse
    {
        $data = $request->validate(['needs' => ['nullable', 'string', 'max:300']]);
        $properties = $this->selected($request);

        if ($properties->count() < 2) {
            return redirect()->route('client.compare')->with('error', 'Add at least two properties to compare first.');
        }

        $needs = trim((string) ($data['needs'] ?? ''));
        $text = $ai->compareProperties($this->comparison->facts($properties), $needs);

        $request->session()->flash('compare_summary', [
            'text' => $text ?? $this->comparison->quickSummary($properties),
            'ai' => $text !== null,
            'needs' => $needs,
        ]);

        return redirect()->to(route('client.compare').'#summary');
    }

    /** The picked listings in the order they were added, dropping any that went away. */
    private function selected(Request $request): Collection
    {
        $ids = collect($request->session()->get(self::SESSION_KEY, []))->filter(fn ($id) => is_int($id))->values();

        $properties = Property::whereIn('id', $ids)
            ->whereNotIn('status', ['hidden', 'flagged'])
            ->withCount(['lots' => fn ($query) => $query->where('status', 'available')])
            ->withRating()
            ->get()
            ->sortBy(fn (Property $property) => $ids->search($property->id))
            ->values();

        if ($properties->count() !== $ids->count()) {
            $request->session()->put(self::SESSION_KEY, $properties->pluck('id')->all());
        }

        return $properties;
    }

    /** Saved and recently viewed listings a client can add with one click. */
    private function suggestions(Request $request, Collection $selected): Collection
    {
        $user = $request->user();

        if ($user?->role !== 'client' || $selected->count() >= PropertyComparison::MAX) {
            return collect();
        }

        $ids = $user->favorites()->latest()->pluck('property_id')
            ->concat(PropertyView::where('user_id', $user->id)->latest('last_viewed_at')->take(10)->pluck('property_id'))
            ->unique()
            ->reject(fn ($id) => $selected->contains('id', $id))
            ->take(6)
            ->values();

        return Property::whereIn('id', $ids)
            ->where('status', 'available')
            ->get()
            ->sortBy(fn (Property $property) => $ids->search($property->id))
            ->values();
    }
}
