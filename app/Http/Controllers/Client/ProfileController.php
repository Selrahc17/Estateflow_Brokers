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
        $user = auth()->user();
        $client = $user->clientProfile;

        $request->validate([
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'email'        => 'required|email|unique:users,email,' . $user->id,
            'phone'        => 'nullable|string|max:20',
            'date_of_birth'=> 'nullable|date',
            'civil_status' => 'nullable|in:single,married,widowed,separated',
            'address'      => 'nullable|string|max:500',
            'avatar'       => 'nullable|image|max:2048',
        ]);

        $name = trim($request->first_name . ' ' . $request->last_name);
        $userData = ['name' => $name, 'email' => $request->email, 'phone' => $request->phone];

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'client-avatars/' . uniqid() . '.' . $file->getClientOriginalExtension();
            \Illuminate\Support\Facades\Storage::disk('supabase')->put($filename, file_get_contents($file->getRealPath()), 'public');
            $userData['avatar'] = 'https://yungpjrhvpjneanvyxnt.supabase.co/storage/v1/object/public/properties/' . $filename;
        }

        $user->update($userData);

        if ($client) {
            $client->update([
                'first_name'    => $request->first_name,
                'last_name'     => $request->last_name,
                'email'         => $request->email,
                'phone'         => $request->phone,
                'date_of_birth' => $request->date_of_birth,
                'civil_status'  => $request->civil_status,
                'address'       => $request->address,
            ]);
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