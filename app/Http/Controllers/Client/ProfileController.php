<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function index(): View
    {
        $user = auth()->user();
        $client = $user->clientProfile;
        return view('pages.client.profile.index', compact('user', 'client'));
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $request->validate([
            'name'  => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . auth()->id(),
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
        ]);

        auth()->user()->update(['name' => $request->name, 'email' => $request->email]);

        $client = auth()->user()->clientProfile;
        if ($client) {
            $client->update(['phone' => $request->phone, 'address' => $request->address]);
        }

        return back()->with('success', 'Profile updated successfully.');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $request->validate([
            'current_password' => 'required|current_password',
            'password'         => 'required|string|min:8|confirmed',
        ]);

        auth()->user()->update(['password' => $request->password]);

        return back()->with('success', 'Password updated successfully.');
    }
}