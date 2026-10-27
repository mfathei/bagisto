<?php

namespace Webkul\Saas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Webkul\Saas\Enums\MembershipStatus;
use Webkul\Saas\Enums\TenantStatus;
use Webkul\Saas\Repositories\TenantRepository;
use Webkul\Saas\Repositories\TenantUserRepository;
use Webkul\Saas\Services\TenantContext;
use Webkul\Saas\Services\TenantResolver;

class ResolveTenantForApi
{
    /**
     * Create a new middleware instance.
     */
    public function __construct(
        protected TenantContext $context,
        protected TenantRepository $tenants,
        protected TenantUserRepository $memberships,
        protected TenantResolver $resolver
    ) {}

    /**
     * Resolve the tenant from the authenticated admin, never from the payload.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->context->clear();

        if (! config('saas.enabled')) {
            return $next($request);
        }

        $admin = Auth::guard('sanctum')->user();

        if (! $admin || ! $admin->tenant_id) {
            return response()->json(['message' => 'This token is not attached to a store.'], 403);
        }

        $tenant = $this->tenants->find($admin->tenant_id);

        if (! $tenant || $tenant->status !== TenantStatus::Active) {
            return response()->json(['message' => trans('saas::app.errors.store-unavailable')], 403);
        }

        $membership = $this->memberships->findMembership($tenant->getKey(), $admin->getKey());

        if (! $membership || $membership->status !== MembershipStatus::Active) {
            return response()->json(['message' => trans('saas::app.errors.membership-inactive')], 403);
        }

        $this->resolver->activate($tenant, 'api');

        try {
            return $next($request);
        } finally {
            $this->context->clear();
        }
    }
}
