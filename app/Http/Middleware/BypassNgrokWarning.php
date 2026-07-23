<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BypassNgrokWarning
{
    /**
     * Add the header that tells ngrok to skip its browser warning interstitial.
     * This is a no-op in production (the header is harmless on non-ngrok hosts).
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('ngrok-skip-browser-warning', 'true');

        return $response;
    }
}
