<?php

namespace App\Http\Middleware;

use App\Domain\Users\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Use the route's authenticated user (sanctum guard on API routes,
        // web guard on web routes) rather than auth()->user(), which only
        // resolves the default (web) guard and is null for token-auth API calls.
        $userRole = $request->user()?->role;

        $allowed = array_map(fn(string $r) => UserRole::from($r), $roles);

        if (! in_array($userRole, $allowed, true)) {
            abort(403, 'Доступ запрещён.');
        }

        return $next($request);
    }
}
