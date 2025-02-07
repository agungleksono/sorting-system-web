<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AuthWeb
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        // // Check if the user is authenticated
        // if (!Auth::check()) {
        //     // Redirect to the login page if the user is not authenticated
        //     return redirect()->route('login')->with('error', 'Please login to access this page.');
        // }

        // Check if the user is authenticated
        if (is_null(session('loggedin')) || session('loggedin') !== true)
        {
            // Redirect to the login page if the user is not authenticated
            return redirect()->route('login')->with('error', 'Please login to access this page.');
        }

        // If the user is authenticated, allow the request to proceed
        return $next($request);
    }
}
