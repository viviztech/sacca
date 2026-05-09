<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            abort(401);
        }

        $allowed = array_map(
            fn (string $r) => UserRole::from($r),
            $roles
        );

        if (! $request->user()->hasRole($allowed)) {
            abort(403, 'Unauthorized.');
        }

        return $next($request);
    }
}
