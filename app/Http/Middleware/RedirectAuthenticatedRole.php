<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectAuthenticatedRole
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $dashboard = match ($user->role) {
                'admin' => route('admin.dashboard'),
                'broker' => route('broker.dashboard'),
                'agent' => route('agent.dashboard'),
                default => null,
            };

            if ($dashboard) {
                return redirect()->to($dashboard);
            }
        }

        return $next($request);
    }
}