<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PermissionMiddleware
{
    public function handle(Request $request, Closure $next, string $routeName)
    {
        if (!auth()->check()) {
            abort(401);
        }

        if (!auth()->user()->hasPermission($routeName)) {
            abort(403, 'Unauthorized');
        }

        return $next($request);
    }
}
