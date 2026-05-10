<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * AdminMiddleware
 *
 * FIXES:
 * - CRITICAL-1: Returns proper redirect for browser requests instead of JSON 401
 * - Checks authentication first, then admin flag
 */
class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login')
                ->with('error', 'Please log in to access this area.');
        }

        if (! Auth::user()->is_admin) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Forbidden. Admin access required.'], 403);
            }
            abort(403, 'Admin access required.');
        }

        return $next($request);
    }
}
