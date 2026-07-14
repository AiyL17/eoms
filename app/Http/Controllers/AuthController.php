<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        $maintenance = Setting::get('maintenance_mode', '0') === '1';
        return view('auth.login', compact('maintenance'));
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required'],
        ]);

        // Block non-admin logins during maintenance
        if (Setting::get('maintenance_mode', '0') === '1') {
            if (Auth::attempt($credentials, false)) {
                if (Auth::user()->role !== 'admin') {
                    Auth::logout();
                    return back()
                        ->withInput($request->only('email', 'remember'))
                        ->withErrors(['email' => 'The system is currently under maintenance. Only administrators can log in.']);
                }
                // Admin — allow through
                $request->session()->regenerate();
                return redirect()->intended(route('dashboard'));
            }
            return back()
                ->withInput($request->only('email', 'remember'))
                ->withErrors(['email' => 'These credentials do not match our records.']);
        }

        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();
            return redirect()->intended(route('dashboard'));
        }

        return back()
            ->withInput($request->only('email', 'remember'))
            ->withErrors(['email' => 'These credentials do not match our records.']);
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
