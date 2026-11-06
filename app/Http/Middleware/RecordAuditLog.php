<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RecordAuditLog
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        $user = $request->user();

        if ($user && in_array($request->method(), ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            $routeName = $request->route()?->getName();
            $action = match ($request->method()) {
                'POST' => 'Created or submitted',
                'PUT', 'PATCH' => 'Updated',
                'DELETE' => 'Deleted',
            };

            AuditLog::create([
                'actor_id' => $user->id,
                'actor_role' => $user->role,
                'method' => $request->method(),
                'route_name' => $routeName,
                'action' => $action,
                'description' => $this->describe($request, $routeName),
                'status_code' => $response->getStatusCode(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $response;
    }

    private function describe(Request $request, ?string $routeName): string
    {
        $description = $routeName ? str_replace(['.', '-', '_'], ' ', $routeName) : $request->path();

        $subject = collect($request->route()?->parameters())->first(fn ($value) => $value instanceof Model);

        return $subject ? "{$description} #{$subject->getKey()}" : $description;
    }
}
