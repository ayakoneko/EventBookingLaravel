<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware that restricts access to organiser-only routes.
 *
 * Ensures the user is authenticated and has the 'organiser' role before
 * proceeding; otherwise redirects to login or aborts with 403.
 */

class EnsureUserIsOrganiser
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Deny guests — redirect them to login for a proper auth flow.
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        // Deny logged-in users who are not organisers.
        if (Auth::user()->type !== 'organiser') {
            abort(403, 'Only organisers can access this page.');
        }
        
        return $next($request);
    }
}
