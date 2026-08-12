<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
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

        return redirect()->intended($this->redirectToDashboard($request));
    }

    /**
     * Role-aware dashboard redirect (extend when staff/customer roles exist).
     */
    protected function redirectToDashboard(Request $request): string
    {
        $user = $request->user();

        // Future examples:
        // if ($user->hasRole('admin')) return route('admin.dashboard');
        // if ($user->hasRole('staff')) return route('staff.dashboard');
        // if ($user->hasRole('customer')) return route('customer.dashboard');

        return route('dashboard', absolute: false);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
