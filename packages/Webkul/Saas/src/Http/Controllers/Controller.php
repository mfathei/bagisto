<?php

namespace Webkul\Saas\Http\Controllers;

use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Facades\Auth;
use Webkul\Saas\Contracts\Tenant;
use Webkul\Saas\Contracts\TenantUser;
use Webkul\Saas\Repositories\TenantUserRepository;
use Webkul\Saas\Services\TenantContext;
use Webkul\User\Contracts\Admin;

abstract class Controller extends BaseController
{
    /**
     * The tenant resolved for the current request.
     */
    protected function tenant(): Tenant
    {
        return app(TenantContext::class)->requireTenant();
    }

    /**
     * The authenticated tenant administrator.
     */
    protected function admin(): ?Admin
    {
        return Auth::guard('admin')->user();
    }

    /**
     * The membership of the authenticated administrator in the current tenant.
     */
    protected function membership(): ?TenantUser
    {
        $admin = $this->admin();

        if (! $admin) {
            return null;
        }

        return app(TenantUserRepository::class)->findMembership($this->tenant()->getKey(), $admin->getKey());
    }

    /**
     * Deny the request unless the current administrator holds an administrative role.
     */
    protected function authorizeAdministrative(): void
    {
        $membership = $this->membership();

        abort_unless(
            $membership && $membership->role->isAdministrative(),
            403,
            trans('saas::app.errors.not-authorized')
        );
    }
}
