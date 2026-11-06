<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePendingBroker
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless($user?->role === 'broker', 403, 'Unauthorized access.');

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('auth.login')->withErrors(['email' => 'Your account has been suspended.']);
        }

        if ($user->is_approved) {
            return redirect()->route('broker.dashboard');
        }

        return $next($request);
    }
}
