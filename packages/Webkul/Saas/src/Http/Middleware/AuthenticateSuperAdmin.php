<?php

namespace Webkul\Saas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateSuperAdmin
{
    /**
     * Guard the platform panel with the isolated Super Admin guard.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $guard = Auth::guard('super_admin');

        if (! $guard->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }

            return redirect()->guest(route('saas.super-admin.login'));
        }

        if (! (bool) $guard->user()->status) {
            $guard->logout();

            if ($request->expectsJson()) {
                return response()->json(['message' => 'This account is disabled.'], 401);
            }

            return redirect()->guest(route('saas.super-admin.login'))
                ->withErrors(['email' => trans('saas::app.super-admin.login.inactive')]);
        }

        return $next($request);
    }
}
