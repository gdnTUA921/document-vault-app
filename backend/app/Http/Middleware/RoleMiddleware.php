<?php

namespace App\Http\Middleware;

use Closure;

class RoleMiddleware
{
    public function handle($request, Closure $next, ...$roles)
    {
        $user = $request->user('api');
        if (!$user) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        // roles can be like: ['admin'] or ['staff|admin'] depending on route
        $allowed = [];
        foreach ($roles as $r) {
            foreach (explode('|', $r) as $piece) {
                $allowed[] = trim($piece);
            }
        }

        if (!in_array($user->role, $allowed, true)) {
            return response()->json(['message' => 'Forbidden'], 403);
        }
        return $next($request);
    }
}

