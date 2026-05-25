<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureParentAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessParentPortal()) {
            return redirect()->route('login')->with('error', 'Parent login required.');
        }

        return $next($request);
    }
}
