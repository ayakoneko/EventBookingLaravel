<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware that restricts access to attendee-only routes.
 *
 * Ensures the user is authenticated and has the 'attendee' role before
 * proceeding; otherwise aborts with 403.
 */

class EnsureUserIsAttendee
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Deny if unauthenticated or role is not 'attendee'.
        if (!auth()->check() || auth()->user()->type !== 'attendee') {
            abort(403, 'Access denied. Attendee only.');
        }
        
        return $next($request);
    }
}
