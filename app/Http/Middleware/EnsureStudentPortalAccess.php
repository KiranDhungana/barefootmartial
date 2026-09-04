<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureStudentPortalAccess
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || ! $user->canAccessStudentPortal()) {
            return redirect()->route('login')->with('error', 'Student or parent login required.');
        }

        return $next($request);
    }
}
