<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Property;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FavoriteController extends Controller
{
    public function index(): View
    {
        $favorites = auth()->user()->favorites()
            ->with('property')
            ->paginate(12);

        return view('pages.client.favorites.index', compact('favorites'));
    }

    public function store(Request $request, Property $property): RedirectResponse
    {
        $exists = Favorite::where('user_id', auth()->id())
            ->where('property_id', $property->id)
            ->exists();

        if (!$exists) {
            Favorite::create([
                'user_id' => auth()->id(),
                'property_id' => $property->id,
            ]);
            
            return redirect()->back()->with('success', 'Property added to favorites!');
        }

        return redirect()->back()->with('info', 'Property already in favorites.');
    }

    public function destroy(Property $property): RedirectResponse
    {
        Favorite::where('user_id', auth()->id())
            ->where('property_id', $property->id)
            ->delete();

        return redirect()->back()->with('success', 'Property removed from favorites.');
    }

    public function toggle(Property $property): RedirectResponse
    {
        $favorite = Favorite::where('user_id', auth()->id())
            ->where('property_id', $property->id)
            ->first();

        if ($favorite) {
            $favorite->delete();
            return redirect()->back()->with('success', 'Property removed from favorites.');
        } else {
            Favorite::create([
                'user_id' => auth()->id(),
                'property_id' => $property->id,
            ]);
            return redirect()->back()->with('success', 'Property added to favorites!');
        }
    }
}
