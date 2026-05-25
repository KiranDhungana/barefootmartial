<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSuperAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || ! $user->isSuperAdmin()) {
            return redirect()->route('erp.dashboard')->with('error', 'Super admin access required.');
        }

        return $next($request);
    }
}
