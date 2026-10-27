<?php

namespace Webkul\Saas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Webkul\Saas\Enums\FeatureKey;
use Webkul\Saas\Services\EntitlementService;
use Webkul\Saas\Services\TenantContext;

class EnsureFeature
{
    /**
     * Create a new middleware instance.
     */
    public function __construct(
        protected TenantContext $context,
        protected EntitlementService $entitlements
    ) {}

    /**
     * Enforce a boolean feature entitlement server-side.
     */
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        $tenant = $this->context->requireTenant();
        $key = FeatureKey::tryFrom($feature);

        if (! $key || ! $this->entitlements->includes($tenant, $key)) {
            abort(403, trans('saas::app.errors.feature-unavailable', ['feature' => $feature]));
        }

        return $next($request);
    }
}
