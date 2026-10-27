<?php

namespace Webkul\Saas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Webkul\Saas\Services\TenantResolver;

class EnsurePlatformHost
{
    /**
     * Create a new middleware instance.
     */
    public function __construct(protected TenantResolver $resolver) {}

    /**
     * Only allow platform-level pages on the platform host.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('saas.enabled')) {
            abort(404);
        }

        if (! $this->resolver->isPlatformHost($request->getHost())) {
            abort(404, trans('saas::app.errors.store-not-found'));
        }

        return $next($request);
    }
}
