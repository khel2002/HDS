<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class SuperAdminMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            abort(401);
        }

        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized access. Super Admin privileges required.');
        }

        return $next($request);
    }
}
