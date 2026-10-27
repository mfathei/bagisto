<?php

namespace Webkul\Saas\Http\Controllers\SuperAdmin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Webkul\Saas\Enums\AuditAction;
use Webkul\Saas\Http\Controllers\Controller;
use Webkul\Saas\Services\AuditLogger;

class AuthController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(protected AuditLogger $audit) {}

    /**
     * Show the platform sign-in form.
     */
    public function create()
    {
        if (Auth::guard('super_admin')->check()) {
            return redirect()->route('saas.super-admin.dashboard');
        }

        return view('saas::super-admin.auth.login');
    }

    /**
     * Authenticate a Super Admin against the isolated guard.
     */
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email', 'max:191'],
            'password' => ['required', 'string'],
        ]);

        $throttleKey = 'saas-super-admin:'.Str::lower($credentials['email']).'|'.$request->ip();

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            throw ValidationException::withMessages([
                'email' => trans('saas::app.super-admin.login.throttled', [
                    'seconds' => RateLimiter::availableIn($throttleKey),
                ]),
            ]);
        }

        if (! Auth::guard('super_admin')->attempt($credentials, $request->boolean('remember'))) {
            RateLimiter::hit($throttleKey, 300);

            throw ValidationException::withMessages([
                'email' => trans('saas::app.super-admin.login.failed'),
            ]);
        }

        $admin = Auth::guard('super_admin')->user();

        if (! (bool) $admin->status) {
            Auth::guard('super_admin')->logout();
            RateLimiter::hit($throttleKey, 300);

            throw ValidationException::withMessages([
                'email' => trans('saas::app.super-admin.login.inactive'),
            ]);
        }

        RateLimiter::clear($throttleKey);

        $request->session()->regenerate();

        $admin->forceFill(['last_login_at' => now()])->save();

        $this->audit->platform(AuditAction::SuperAdminLogin, [
            'description' => 'Super Admin signed in.',
        ]);

        return redirect()->intended(route('saas.super-admin.dashboard'));
    }

    /**
     * End the platform session.
     */
    public function destroy(Request $request)
    {
        Auth::guard('super_admin')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('saas.super-admin.login');
    }
}
