<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        return view('pages.admin.settings.index');
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

        auth()->user()->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password updated successfully.');
    }

    public function updateLogo(Request $request): RedirectResponse
    {
        $request->validate(['logo' => 'required|image|max:2048']);

        $file = $request->file('logo');
        $filename = 'logos/logo_' . time() . '.' . $file->getClientOriginalExtension();
        Storage::disk('supabase')->put($filename, file_get_contents($file));

        $baseUrl = rtrim(str_replace('/storage/v1/s3', '', config('filesystems.disks.supabase.endpoint')), '/');
        Setting::set('logo_url', $baseUrl . '/storage/v1/object/public/properties/' . $filename);

        return back()->with('success', 'Logo updated successfully.');
    }

    public function updateChatbotLogo(Request $request): RedirectResponse
    {
        $request->validate(['chatbot_logo' => 'required|image|max:2048']);

        $file = $request->file('chatbot_logo');
        $filename = 'logos/chatbot_' . time() . '.' . $file->getClientOriginalExtension();
        Storage::disk('supabase')->put($filename, file_get_contents($file));

        $baseUrl = rtrim(str_replace('/storage/v1/s3', '', config('filesystems.disks.supabase.endpoint')), '/');
        Setting::set('chatbot_logo', $baseUrl . '/storage/v1/object/public/properties/' . $filename);

        return back()->with('success', 'Chatbot logo updated successfully.');
    }
}
