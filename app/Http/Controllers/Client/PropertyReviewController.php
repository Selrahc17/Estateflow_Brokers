<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\AppNotification;
use App\Models\Property;
use App\Models\PropertyReview;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PropertyReviewController extends Controller
{
    /** Saves the client's review, or updates the one they already left. */
    public function store(Request $request, Property $property): RedirectResponse
    {
        abort_if(in_array($property->status, ['hidden', 'flagged'], true), 404);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
            'comment' => ['nullable', 'string', 'max:1000'],
        ], [
            'rating.required' => 'Choose a star rating from 1 to 5.',
            'rating.between' => 'Choose a star rating from 1 to 5.',
        ]);

        $review = PropertyReview::firstOrNew(['property_id' => $property->id, 'user_id' => $request->user()->id]);
        $isNew = ! $review->exists;
        $review->fill([
            'rating' => $data['rating'],
            'comment' => filled($data['comment'] ?? null) ? trim($data['comment']) : null,
        ])->save();

        if ($isNew && $property->broker_id) {
            AppNotification::create([
                'user_id' => $property->broker_id,
                'type' => 'property_review',
                'title' => 'New review on '.$property->name,
                'message' => "{$request->user()->name} rated {$property->name} {$data['rating']} out of 5.",
                'is_read' => false,
            ]);
        }

        return redirect()->to(route('client.property.show', $property->slug).'#reviews')
            ->with('success', $isNew ? 'Thanks! Your review is now posted.' : 'Your review has been updated.');
    }

    public function destroy(Request $request, Property $property): RedirectResponse
    {
        PropertyReview::where('property_id', $property->id)->where('user_id', $request->user()->id)->delete();

        return redirect()->to(route('client.property.show', $property->slug).'#reviews')
            ->with('success', 'Your review has been removed.');
    }
}
