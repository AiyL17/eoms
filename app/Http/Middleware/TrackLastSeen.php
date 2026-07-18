<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TrackLastSeen
{
    /**
     * Update the authenticated user's last_seen_at timestamp.
     * Uses a per-user cache lock so we only write to the DB at most
     * once every 2 minutes, keeping overhead negligible.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user    = Auth::user();
            $cacheKey = 'last_seen_' . $user->id;

            if (! Cache::has($cacheKey)) {
                $user->timestamps = false;           // don't bump updated_at
                $user->last_seen_at = now();
                $user->save();

                Cache::put($cacheKey, true, now()->addMinutes(2));
            }
        }

        return $next($request);
    }
}
