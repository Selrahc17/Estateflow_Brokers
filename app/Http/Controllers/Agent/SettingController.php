<?php

namespace App\Http\Controllers\Agent;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        return view('pages.settings.index');
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name'   => 'required|string|max:255',
            'email'  => 'required|email|unique:users,email,' . auth()->id(),
            'phone'  => 'nullable|string|max:20',
            'avatar' => 'nullable|image|max:2048',
        ]);

        $update = ['name' => $data['name'], 'email' => $data['email'], 'phone' => $data['phone'] ?? null];

        if ($request->hasFile('avatar')) {
            $file = $request->file('avatar');
            $filename = 'avatars/' . auth()->id() . '_' . time() . '.' . $file->getClientOriginalExtension();
            Storage::disk('supabase')->put($filename, file_get_contents($file));
            $baseUrl = rtrim(str_replace('/storage/v1/s3', '', config('filesystems.disks.supabase.endpoint')), '/');
            $update['avatar'] = $baseUrl . '/storage/v1/object/public/properties/' . $filename;
        }

        auth()->user()->update($update);

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
