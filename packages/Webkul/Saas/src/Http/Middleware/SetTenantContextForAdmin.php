<?php

namespace Webkul\Saas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;
use Webkul\Saas\Enums\MembershipStatus;
use Webkul\Saas\Enums\TenantStatus;
use Webkul\Saas\Repositories\TenantRepository;
use Webkul\Saas\Repositories\TenantUserRepository;
use Webkul\Saas\Services\TenantContext;
use Webkul\Saas\Services\TenantResolver;

class SetTenantContextForAdmin
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
     * Activate the tenant of the signed-in admin, denying suspended tenants.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->context->clear();

        $admin = Auth::guard('admin')->user();

        if (! config('saas.enabled') || ! $admin || ! $admin->tenant_id) {
            return $next($request);
        }

        $tenant = $this->tenants->find($admin->tenant_id);

        if (! $tenant) {
            Auth::guard('admin')->logout();

            return $this->deny($request, trans('saas::app.errors.store-unavailable'), 403);
        }

        if ($tenant->status === TenantStatus::Suspended || $tenant->status === TenantStatus::Cancelled) {
            Auth::guard('admin')->logout();

            return $this->deny($request, trans('saas::app.errors.tenant-suspended'), 403);
        }

        if ($tenant->status !== TenantStatus::Active) {
            return $this->deny($request, trans('saas::app.errors.tenant-not-active'), 403);
        }

        $membership = $this->memberships->findMembership($tenant->getKey(), $admin->getKey());

        if (! $membership || $membership->status !== MembershipStatus::Active) {
            return $this->deny($request, trans('saas::app.errors.membership-inactive'), 403);
        }

        $this->resolver->activate($tenant, 'admin');

        if (! $membership->last_active_at || $membership->last_active_at->lt(now()->subMinutes(5))) {
            $membership->forceFill(['last_active_at' => now()])->saveQuietly();
        }

        View::share('saasTenant', $tenant);
        View::share('saasMembership', $membership);
        View::share('saasPlatformName', config('saas.platform_name'));

        try {
            return $next($request);
        } finally {
            $this->context->clear();
        }
    }

    /**
     * Reject the request without leaking tenant details.
     */
    protected function deny(Request $request, string $message, int $status): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], $status);
        }

        abort($status, $message);
    }
}
