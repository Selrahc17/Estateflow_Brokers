<?php

namespace App\Services;

use App\Models\Favorite;
use App\Models\Inquiry;
use App\Models\Property;
use App\Models\PropertyView;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * "Recommended for you" on the client dashboard.
 *
 * Learns what a client likes from the listings they saved, asked about,
 * reserved and opened, then asks the AI recommender to rank the rest.
 * A client with no activity yet gets the most popular listings instead.
 */
class PropertyRecommender
{
    public const LIMIT = 6;

    /** How much each kind of activity says about what the client wants. */
    private const WEIGHTS = ['reserved' => 4, 'saved' => 3, 'asked' => 3, 'viewed' => 1];

    public function __construct(private AIService $ai) {}

    /**
     * @return array{mode: string, properties: Collection, activity: array<string, int>, preferences: array<string, mixed>}
     */
    public function for(User $user): array
    {
        $signals = $this->signals($user);

        if ($signals->isEmpty()) {
            return ['mode' => 'popular', 'properties' => $this->popular(), 'activity' => [], 'preferences' => [], 'ai' => false];
        }

        $seeds = Property::whereIn('id', $signals->keys())->get()->keyBy('id');
        $preferences = $this->preferences($seeds, $signals);

        // Same activity, same picks: reuse them for a few hours instead of asking the AI on every visit.
        $fingerprint = md5($signals->sortKeys()->toJson());
        $picked = Cache::remember("recommendations:{$user->id}:{$fingerprint}", now()->addHours(6), function () use ($preferences, $signals) {
            $ranked = $this->ai->recommendProperties($preferences);

            return [
                'ids' => $ranked->reject(fn (Property $property) => $signals->has($property->id))->take(self::LIMIT)->pluck('id')->all(),
                'ai' => $this->ai->lastRankedByAi,
            ];
        });

        $properties = $this->load($picked['ids']);

        // Too few new matches: top up with popular listings the client has not seen.
        if ($properties->count() < 3) {
            $properties = $properties->concat(
                $this->popular($signals->keys()->concat($properties->pluck('id')))->take(self::LIMIT - $properties->count())
            );
        }

        return [
            'mode' => 'personal',
            'properties' => $properties,
            'activity' => $this->activityCounts($user),
            'preferences' => $preferences,
            'ai' => $picked['ai'],
        ];
    }

    /** Most saved and asked-about listings, for clients with no activity yet. */
    public function popular(?Collection $exclude = null): Collection
    {
        return Property::where('status', 'available')
            ->when($exclude?->isNotEmpty(), fn ($query) => $query->whereNotIn('id', $exclude))
            ->withCount(['favorites', 'inquiries', 'lots' => fn ($query) => $query->where('status', 'available')])
            ->withRating()
            ->latest()
            ->take(60)
            ->get()
            ->sortByDesc(fn (Property $property) => $property->favorites_count * 3 + $property->inquiries_count * 2 + min((int) $property->view_count, 500) / 25)
            ->take(self::LIMIT)
            ->values();
    }

    /** @return Collection<int, int> property id => weight */
    private function signals(User $user): Collection
    {
        $weights = collect();
        $add = function (iterable $ids, int $weight) use ($weights) {
            foreach ($ids as $id) {
                $weights[$id] = ($weights[$id] ?? 0) + $weight;
            }
        };

        $add(Favorite::where('user_id', $user->id)->pluck('property_id'), self::WEIGHTS['saved']);
        $add(Inquiry::where('user_id', $user->id)->whereNotNull('property_id')->pluck('property_id'), self::WEIGHTS['asked']);
        $add(Reservation::whereIn('client_id', $user->clientProfiles()->select('id'))
            ->join('lots', 'lots.id', '=', 'reservations.lot_id')
            ->pluck('lots.property_id'), self::WEIGHTS['reserved']);

        foreach (PropertyView::where('user_id', $user->id)->latest('last_viewed_at')->take(30)->get(['property_id', 'visits']) as $view) {
            $add([$view->property_id], self::WEIGHTS['viewed'] * min($view->visits, 3));
        }

        return $weights;
    }

    /**
     * What the activity points to: the most common type and place, a price
     * ceiling a little above the usual price, and the usual bedroom count.
     *
     * @return array<string, mixed>
     */
    private function preferences(Collection $seeds, Collection $signals): array
    {
        $weighted = fn (string $field) => $seeds
            ->filter(fn (Property $property) => filled($property->{$field}))
            ->groupBy($field)
            ->map(fn (Collection $group) => $group->sum(fn (Property $property) => $signals[$property->id] ?? 0))
            ->sortDesc()
            ->keys()
            ->first();

        $prices = $seeds->map(fn (Property $property) => (float) $property->price ?: $property->lowest_model_price)->filter()->sort()->values();
        $bedrooms = $seeds->pluck('bedrooms')->filter()->sort()->values();

        return array_filter([
            'type' => $weighted('type'),
            'city' => $weighted('city'),
            'province' => $weighted('province'),
            'max_price' => $prices->isNotEmpty() ? round($prices[intdiv($prices->count(), 2)] * 1.3) : null,
            'bedrooms' => $bedrooms->isNotEmpty() ? (int) $bedrooms[intdiv($bedrooms->count(), 2)] : null,
        ]);
    }

    /** @return array<string, int> */
    private function activityCounts(User $user): array
    {
        return array_filter([
            'saved' => Favorite::where('user_id', $user->id)->count(),
            'viewed' => PropertyView::where('user_id', $user->id)->count(),
            'asked about' => Inquiry::where('user_id', $user->id)->distinct()->count('property_id'),
        ]);
    }

    private function load(array $ids): Collection
    {
        return Property::whereIn('id', $ids)
            ->where('status', 'available')
            ->withCount(['lots' => fn ($query) => $query->where('status', 'available')])
            ->withRating()
            ->get()
            ->sortBy(fn (Property $property) => array_search($property->id, $ids, true))
            ->values();
    }
}
