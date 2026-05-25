<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCanManageFinance
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || ! $user->canManageFinance()) {
            return redirect()->route('erp.dashboard')->with('error', 'That area is limited to finance roles.');
        }

        return $next($request);
    }
}
