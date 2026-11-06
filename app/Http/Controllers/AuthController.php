<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use App\Services\BrokerComplianceService;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
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

    // Handle registration. Agents are added by their broker, so they cannot sign up here.
    public function register(Request $request, BrokerComplianceService $compliance): RedirectResponse
    {
        $isBroker = $request->input('role') === 'broker';

        $rules = [
            'first_name' => 'required|string|max:100',
            'last_name'  => 'required|string|max:100',
            'email'      => 'required|email|unique:users,email',
            'phone'      => 'nullable|string|max:20',
            'password'   => 'required|string|min:8|confirmed',
            'role'       => 'required|in:client,broker',
            'privacy_consent' => 'accepted',
        ];

        if ($isBroker) {
            $rules = [...$rules, ...$compliance->detailRules(), 'prc_id_file' => BrokerComplianceService::FILE_RULES];
        }

        $data = $request->validate($rules, [
            ...$compliance->messages(),
            'email.unique' => 'An account with this email already exists. Try signing in instead.',
            'password.min' => 'Your password must be at least 8 characters.',
            'password.confirmed' => 'The passwords do not match.',
            'privacy_consent.accepted' => 'Please agree to the Privacy Policy to continue.',
        ], $compliance->attributes());

        $user = DB::transaction(function () use ($data, $isBroker, $request, $compliance) {
            $hashedPassword = Hash::make($data['password']);
            $brokerFields = $isBroker
                ? Arr::only($data, ['license_number', 'prc_id_expires_at', 'dhsud_registration_number', 'dhsud_registration_expires_at', 'tin'])
                : [];

            $user = User::create([
                'name'     => $data['first_name'] . ' ' . $data['last_name'],
                'email'    => $data['email'],
                'phone'    => $data['phone'] ?? null,
                'password' => $hashedPassword,
                'role'     => $data['role'],
                'is_approved' => ! $isBroker,
                'is_active' => true,
                'privacy_consented_at' => now(),
                ...$brokerFields,
            ]);

            if ($isBroker) {
                $compliance->storeDocument($user, 'prc_id', $request->file('prc_id_file'));
            } else {
                Client::create([
                    'user_id'    => $user->id,
                    'first_name' => $data['first_name'],
                    'last_name'  => $data['last_name'],
                    'email'      => $data['email'],
                    'phone'      => $data['phone'] ?? null,
                    'password'   => $hashedPassword,
                    'status'     => 'active',
                ]);
            }

            return $user;
        });

        Auth::login($user);
        $request->session()->regenerate();

        return redirect($this->redirectForRole($user));
    }

    // Handle login
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Please enter your password.',
        ]);

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $user = Auth::user();

            // Check if user is active
            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your account has been suspended. Please contact support.',
                ])->onlyInput('email');
            }

            // Pending brokers may sign in to finish their application; pending agents may not.
            if ($user->role === 'agent' && !$user->is_approved) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Your agent account is not active yet. Please contact your broker.',
                ])->onlyInput('email');
            }

            $request->session()->regenerate();
            return redirect($this->redirectForRole($user));
        }

        return back()->withErrors([
            'email' => 'Incorrect email or password. Please try again.',
        ])->onlyInput('email');
    }

    // Handle logout
    public function logout(Request $request): RedirectResponse
    {
        $isStaff = in_array($request->user()?->role, ['agent', 'broker'], true);

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return $isStaff
            ? redirect()->route('auth.login')->with('success', 'You have been signed out.')
            : redirect('/');
    }

    // Redirect based on role
    private function redirectForRole(User $user): string
    {
        return match ($user->role) {
            'admin'  => route('admin.dashboard'),
            'broker' => $user->is_approved ? route('broker.dashboard') : route('broker.application.show'),
            'agent'  => route('agent.dashboard'),
            default  => route('client.properties'),
        };
    }

    public function showForgotForm(): View
    {
        return view('pages.auth.forgot');
    }

    public function sendResetLink(Request $request): RedirectResponse
    {
        $request->validate(['email' => 'required|email'], [
            'email.required' => 'Please enter your email address.',
            'email.email' => 'Please enter a valid email address.',
        ]);

        // The reply and its timing must not reveal whether the email is registered,
        // so the link is sent after the response and every outcome reads the same.
        $credentials = $request->only('email');
        app()->terminating(fn () => rescue(fn () => Password::sendResetLink($credentials)));

        return back()->with('success', "If that email is registered, we've sent a password reset link.");
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
        ], [
            'password.min' => 'Your password must be at least 8 characters.',
            'password.confirmed' => 'The passwords do not match.',
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
            : back()->withErrors(['email' => 'This reset link is invalid or has expired.']);
    }
    }