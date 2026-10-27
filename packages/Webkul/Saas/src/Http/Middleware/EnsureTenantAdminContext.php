<?php

namespace Webkul\Saas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Webkul\Saas\Services\TenantContext;

class EnsureTenantAdminContext
{
    /**
     * Create a new middleware instance.
     */
    public function __construct(protected TenantContext $context) {}

    /**
     * Refuse tenant panel routes when no tenant context is active.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $this->context->check()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'This account is not attached to a store.'], 403);
            }

            abort(403, trans('saas::app.errors.tenant-not-active'));
        }

        return $next($request);
    }
}
