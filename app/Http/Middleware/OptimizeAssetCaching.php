<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class OptimizeAssetCaching
{
    /**
     * Add caching headers untuk asset yang static
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        // Use $response->headers->set() instead of $response->header()
        // to support StreamedResponse which doesn't have the header() method

        // Cache CSS, JS, images untuk 30 hari
        if ($request->is('css/*', 'js/*', 'images/*', 'build/*')) {
            $response->headers->set('Cache-Control', 'public, max-age=2592000, immutable');
        }
        // Cache API responses untuk 5 menit
        elseif ($request->is('api/*')) {
            $response->headers->set('Cache-Control', 'private, max-age=300');
        }
        // Dashboard & pages - no cache untuk freshness
        else {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }

        return $response;
    }
}
