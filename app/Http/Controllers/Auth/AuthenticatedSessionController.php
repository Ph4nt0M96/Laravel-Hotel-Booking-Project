<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->ban_status == 1) {
            Auth::logout();
            return redirect()->back()->with('banned', true)
                ->with('message', 'Your account has been banned. Please contact support at mythicalhotel@support.com for assistance.');
        }

        // Check the user's role
        if ($user->role == 1) {
            return redirect()->intended(RouteServiceProvider::ADMIN_HOME);
        }

        if ($user->role == 0) {
            // Redirect regular users to the Breeze dashboard
            return redirect()->intended(RouteServiceProvider::HOME);
        }

        return redirect()->route('login')->with('error', 'Invalid role.');
    }


    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
