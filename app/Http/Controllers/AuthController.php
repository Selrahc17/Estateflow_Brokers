<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
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
        $hashedPassword = Hash::make($request->password);

        $user = User::create([
            'name'     => $fullName,
            'email'    => $request->email,
            'password' => $hashedPassword,
            'role'     => $request->role,
            'is_approved' => $request->role === 'broker' ? false : true,
            'is_active' => true,
        ]);

        if ($request->role === 'client') {
            Client::create([
                'user_id'    => $user->id,
                'first_name' => $request->first_name,
                'last_name'  => $request->last_name,
                'email'      => $request->email,
                'phone'      => $request->phone,
                'password'   => $hashedPassword,
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
            $user = Auth::user();
            
            // Check if user is active
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account has been suspended.',
                ])->onlyInput('email');
            }

            // Check if broker is approved
            if ($user->role === 'broker' && !$user->is_approved) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your broker account is pending approval.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            return redirect()->intended($this->redirectForRole($user));
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

    public function showForgotForm(): View
    {
        return view('pages.auth.forgot');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? back()->with('success', 'Password reset link sent to your email.')
            : back()->withErrors(['email' => __($status)]);
    }

    public function showResetForm(Request $request, string $token): View
    {
        return view('pages.auth.reset.index', ['token' => $token, 'email' => $request->email]);
    }

    public function resetPassword(Request $request): RedirectResponse
    {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill(['password' => Hash::make($password)])
                     ->setRememberToken(Str::random(60));
                $user->save();
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('auth.login')->with('success', 'Password reset successfully. Please log in.')
            : back()->withErrors(['email' => __($status)]);
    }
    }