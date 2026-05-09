<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BranchScope
{
    /**
     * Binds the authenticated user's branch into the container.
     * Super admins have no branch restriction (branch_id stays null).
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->user()) {
            app()->instance('current_branch_id', $request->user()->branch_id);
        }

        return $next($request);
    }
}
