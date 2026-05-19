<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || ! $user->isAdmin()) {
            return redirect()->route('erp.dashboard')->with('error', 'That area is limited to administrators.');
        }

        return $next($request);
    }
}
