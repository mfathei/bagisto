<?php

namespace Webkul\Saas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdminAbility
{
    /**
     * Authorise a platform ability for the signed-in Super Admin.
     */
    public function handle(Request $request, Closure $next, string $ability): Response
    {
        $user = Auth::guard('super_admin')->user();

        if (! $user || ! $user->hasAbility($ability)) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'This action is unauthorized.'], 403);
            }

            abort(403, 'This action is unauthorized.');
        }

        return $next($request);
    }
}
