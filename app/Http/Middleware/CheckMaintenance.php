<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckMaintenance
{
    public function handle(Request $request, Closure $next)
    {
        if (Setting::get('maintenance_mode', '0') === '1') {
            // Allow admins through
            if (Auth::check() && Auth::user()->role === 'admin') {
                return $next($request);
            }

            // Log out any non-admin who is currently authenticated
            if (Auth::check()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login');
            }
        }

        return $next($request);
    }
}
