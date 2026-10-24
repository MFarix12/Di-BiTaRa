<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        Auth::shouldUse('web');
        $data = $request->validate(['identity' => 'required|string|max:254', 'password' => 'required|string|max:255']);
        $identity = Str::lower(trim($data['identity']));
        $key = hash('sha256', $identity.'|'.$request->ip());
        if (RateLimiter::tooManyAttempts($key, 5)) {
            throw ValidationException::withMessages(['identity' => 'Terlalu banyak cubaan. Cuba semula selepas '.RateLimiter::availableIn($key).' saat.']);
        }
        $field = str_contains($identity, '@') ? 'email' : 'username';
        $adminPortal = $request->routeIs('admin.login.submit');
        $credentials = [$field => $identity, 'password' => $data['password'], 'status' => 'active'];
        // The portal's role comes from the server route, never a submitted form field.
        $credentials['role'] = 'admin';
        if (! Auth::attempt($credentials,$request->boolean('remember'))) {
            RateLimiter::hit($key, 60);
            throw ValidationException::withMessages(['identity' => 'Maklumat log masuk tidak sah.']);
        }
        RateLimiter::clear($key);
        $request->session()->forget(['registration','learner_token']);
        $request->session()->regenerate();
        $request->user()->forceFill(['last_login_at' => now()])->save();
        if ($request->user()->role === 'pelajar') { return redirect()->route('student.dashboard'); }
        return redirect()->route($adminPortal ? 'admin.dashboard' : 'dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('home');
    }
}
