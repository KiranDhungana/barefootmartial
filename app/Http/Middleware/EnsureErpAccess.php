<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureErpAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessErp()) {
            return redirect()->route('home')->with('error', 'You do not have access to the academy console.');
        }

        return $next($request);
    }
}
