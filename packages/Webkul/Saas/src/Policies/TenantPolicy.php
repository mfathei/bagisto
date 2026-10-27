<?php

namespace Webkul\Saas\Policies;

use Webkul\Saas\Contracts\Tenant;
use Webkul\Saas\Models\SuperAdmin;
use Webkul\User\Contracts\Admin;

class TenantPolicy
{
    /**
     * Whether the actor may browse the tenant listing.
     */
    public function viewAny(mixed $user): bool
    {
        return $this->platformAbility($user, 'tenants.view')
            || $user instanceof Admin;
    }

    /**
     * Whether the actor may inspect a tenant.
     */
    public function view(mixed $user, Tenant $tenant): bool
    {
        return $this->platformAbility($user, 'tenants.view')
            || $this->belongsToTenant($user, $tenant);
    }

    /**
     * Whether the actor may change tenant details.
     */
    public function update(mixed $user, Tenant $tenant): bool
    {
        return $this->platformAbility($user, 'tenants.manage')
            || $this->belongsToTenant($user, $tenant);
    }

    /**
     * Whether the actor may suspend, cancel or delete a tenant.
     */
    public function delete(mixed $user, Tenant $tenant): bool
    {
        return $this->platformAbility($user, 'tenants.manage');
    }

    /**
     * Whether the actor may manage billing for a tenant.
     */
    public function manageBilling(mixed $user, Tenant $tenant): bool
    {
        return $this->platformAbility($user, 'billing.manage')
            || $this->belongsToTenant($user, $tenant);
    }

    /**
     * Whether the actor may manage domains for a tenant.
     */
    public function manageDomains(mixed $user, Tenant $tenant): bool
    {
        return $this->platformAbility($user, 'tenants.manage')
            || $this->belongsToTenant($user, $tenant);
    }

    /**
     * Check a platform ability for a Super Admin actor.
     */
    protected function platformAbility(mixed $user, string $ability): bool
    {
        return $user instanceof SuperAdmin && $user->hasAbility($ability);
    }

    /**
     * Check that a Bagisto admin is attached to the given tenant.
     */
    protected function belongsToTenant(mixed $user, Tenant $tenant): bool
    {
        return $user instanceof Admin
            && (int) $user->tenant_id === (int) $tenant->getKey();
    }
}
