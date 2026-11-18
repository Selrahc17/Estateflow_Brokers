<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PropertyReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PropertyReviewController extends Controller
{
    public const TABS = ['all' => 'All', 'visible' => 'Visible', 'hidden' => 'Hidden'];

    public function index(Request $request): View
    {
        $status = array_key_exists((string) $request->input('status'), self::TABS) ? $request->input('status') : 'all';
        $rating = in_array((int) $request->input('rating'), [1, 2, 3, 4, 5], true) ? (int) $request->input('rating') : null;
        $search = trim((string) $request->input('q'));

        $scope = fn () => PropertyReview::query()
            ->when($rating, fn ($query) => $query->where('rating', $rating))
            ->when($search !== '', fn ($query) => $query->where(fn ($match) => $match
                ->where('comment', 'like', "%{$search}%")
                ->orWhereHas('user', fn ($user) => $user->where('name', 'like', "%{$search}%"))
                ->orWhereHas('property', fn ($property) => $property->where('name', 'like', "%{$search}%"))));

        $counts = [
            'all' => $scope()->count(),
            'visible' => $scope()->whereNull('hidden_at')->count(),
            'hidden' => $scope()->whereNotNull('hidden_at')->count(),
        ];

        $reviews = $scope()
            ->with(['user:id,name,email', 'property:id,name,slug'])
            ->when($status === 'visible', fn ($query) => $query->whereNull('hidden_at'))
            ->when($status === 'hidden', fn ($query) => $query->whereNotNull('hidden_at'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('pages.admin.reviews.index', [
            'reviews' => $reviews,
            'tabs' => self::TABS,
            'status' => $status,
            'counts' => $counts,
            'rating' => $rating,
            'search' => $search,
        ]);
    }

    public function hide(PropertyReview $review): RedirectResponse
    {
        $review->update(['hidden_at' => now()]);

        return back()->with('success', "Review by {$review->user?->name} is now hidden from the property page.");
    }

    public function restore(PropertyReview $review): RedirectResponse
    {
        $review->update(['hidden_at' => null]);

        return back()->with('success', "Review by {$review->user?->name} is visible again.");
    }
}
