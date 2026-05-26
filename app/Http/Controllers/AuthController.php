<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AuthController extends Controller
{
    // Show login form
    public function showLoginForm(): View
    {
        return view('pages.auth.login');
    }

    // Show registration form
    public function showRegisterForm(): View
    {
        return view('pages.auth.register');
    }

    // Handle registration
    public function register(Request $request): RedirectResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'nullable|string|max:20',
            'password'   => 'required|string|min:8|confirmed',
            'role'       => 'required|in:client,broker',
        ]);

        $fullName = $request->first_name . ' ' . $request->last_name;

        $user = User::create([
            'name'     => $fullName,
            'email'    => $request->email,
            'password' => $request->password,
            'role'     => $request->role,
        ]);

        if ($request->role === 'client') {
            Client::create([
                'user_id'    => $user->id,
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'email'      => $request->email,
                'phone'      => $request->phone,
                'password'   => $request->password,
                'status'     => 'active',
            ]);
        }

        Auth::login($user);

        return redirect($this->redirectForRole($user));
    }

    // Handle login
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended($this->redirectForRole(Auth::user()));
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our records.',
        ])->onlyInput('email');
    }

    // Handle logout
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }

    // Redirect based on role
    private function redirectForRole(User $user): string
    {
        return match ($user->role) {
            'admin'  => route('admin.dashboard'),
            'broker' => route('broker.dashboard'),
            default  => route('client.properties'),
        };
    }
}