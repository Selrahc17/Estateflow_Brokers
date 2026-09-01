<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  array<int, string>  $roles
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, $roles)) {
            abort(403, 'Unauthorized access.');
        }

        if (! $user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect()->route('auth.login')->withErrors(['email' => 'Your account has been suspended.']);
        }

        if (in_array($user->role, ['agent', 'broker'], true) && ! $user->is_approved) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $roleLabel = $user->role === 'broker' ? 'broker' : 'agent';
            return redirect()->route('auth.login')->withErrors(['email' => "Your {$roleLabel} account is pending approval."]);
        }

        return $next($request);
    }
}
