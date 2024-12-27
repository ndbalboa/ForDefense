<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PreventBrowserCache
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        return $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, private')
                        ->header('Pragma', 'no-cache')
                        ->header('Expires', '0');
    }
}
