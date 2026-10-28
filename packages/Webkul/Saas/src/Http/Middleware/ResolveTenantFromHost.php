<?php

namespace Webkul\Saas\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;
use Webkul\Saas\Services\TenantContext;
use Webkul\Saas\Services\TenantResolver;

class ResolveTenantFromHost
{
    /**
     * Create a new middleware instance.
     */
    public function __construct(
        protected TenantResolver $resolver,
        protected TenantContext $context
    ) {}

    /**
     * Resolve the tenant from the validated host and isolate the session.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('saas.enabled')) {
            return $next($request);
        }

        if ($this->isPlatformPanelRequest($request)) {
            return $next($request);
        }

        $host = $request->getHost();

        if ($this->resolver->isPlatformHost($host)) {
            $this->context->set(null, 'platform');

            if ($request->expectsJson()) {
                return response()->json(['message' => trans('saas::app.errors.store-not-found')], 404);
            }

            abort(404, trans('saas::app.errors.store-not-found'));
        }

        $result = $this->resolver->resolveWithReason($host);

        if (! $result['tenant']) {
            $status = $result['reason'] === 'suspended' ? 503 : 404;

            if ($request->expectsJson()) {
                return response()->json(['message' => trans('saas::app.errors.store-not-found')], $status);
            }

            abort($status, trans('saas::app.errors.store-not-found'));
        }

        $tenant = $result['tenant'];

        $this->isolateSession($request, $tenant);

        $this->resolver->activate($tenant);

        View::share('saasTenant', $tenant);
        View::share('saasPlatformName', config('saas.platform_name'));

        try {
            return $next($request);
        } finally {
            $this->context->clear();
        }
    }

    /**
     * Whether the request targets the admin or Super Admin panel.
     */
    protected function isPlatformPanelRequest(Request $request): bool
    {
        $path = trim($request->path(), '/');

        $adminPrefix = trim((string) config('app.admin_url'), '/');
        $superAdminPrefix = trim((string) config('saas.super_admin_prefix'), '/');

        return ($adminPrefix && ($path === $adminPrefix || str_starts_with($path, $adminPrefix.'/')))
            || ($superAdminPrefix && ($path === $superAdminPrefix || str_starts_with($path, $superAdminPrefix.'/')));
    }

    /**
     * Prevent a shared cookie from carrying a session between tenants.
     */
    protected function isolateSession(Request $request, $tenant): void
    {
        if (! $request->hasSession()) {
            return;
        }

        $session = $request->session();
        $previousTenantId = $session->get('saas_tenant_id');

        if ((int) $previousTenantId === (int) $tenant->getKey()) {
            return;
        }

        if (Auth::guard('customer')->check()) {
            Auth::guard('customer')->logout();
        }

        $session->forget(['cart', 'coupon_code', 'locale', 'currency', 'url.intended']);

        if ($previousTenantId !== null) {
            $session->regenerate();
        }

        $session->put('saas_tenant_id', $tenant->getKey());
    }
}
