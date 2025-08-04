<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RedirectAuthenticatedUserWeb
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
        // Check if the user is authenticated
        if (session('loggedin'))
        {
            // Redirect if the user is authenticated
            return redirect()->route('suspects.index');
        }

        return $next($request);
    }
}
