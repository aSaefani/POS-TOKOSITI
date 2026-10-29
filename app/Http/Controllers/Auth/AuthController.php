<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->ensureIsNotRateLimited();

        if (! Auth::attempt($request->credentials(), $request->boolean('remember'))) {
            $request->registerFailedAttempt();

            return back()
                ->withInput($request->safe()->except('password'))
                ->withErrors(['email' => 'Email atau kata sandi tidak sesuai.']);
        }

        $request->clearAttempts();
        $request->session()->regenerate(); // cegah session fixation

        $user = $request->user();
        $user->forceFill(['last_login_at' => now()])->saveQuietly();

        return redirect()->intended(route($user->role->homeRoute()));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
