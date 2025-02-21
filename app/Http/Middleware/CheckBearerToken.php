<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;

class CheckBearerToken
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
        $requestToken = $request->bearerToken();
        $validToken = env('BEARER_TOKEN');

        if (!$requestToken || $requestToken != $validToken) {
            return ResponseFormatter::error(null, 'Unauthorize', 400);
        }
        
        return $next($request);
    }
}
