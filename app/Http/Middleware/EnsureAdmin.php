<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class EnsureAdmin
{
    /**
     * Handle an incoming request.
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if the user is logged in and if their role is not admin (e.g., role 1 is admin)
        if (Auth::check() && Auth::user()->role != 1) {
            // Redirect to the home page or wherever you want
            return redirect()->back()->with('error', 'You are not authorized to access this page.');
        }

        return $next($request);
    }
}
