<?php

namespace Webkul\Saas\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Saas\Contracts\TenantUser;
use Webkul\Saas\Enums\MembershipRole;
use Webkul\Saas\Enums\MembershipStatus;

class TenantUserRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return TenantUser::class;
    }

    /**
     * Active memberships of a tenant.
     */
    public function activeForTenant(int $tenantId)
    {
        return $this->model
            ->with('admin')
            ->where('tenant_id', $tenantId)
            ->where('status', MembershipStatus::Active->value)
            ->get();
    }

    /**
     * Find the membership linking an admin to a tenant.
     */
    public function findMembership(int $tenantId, int $adminId): ?TenantUser
    {
        return $this->model
            ->where('tenant_id', $tenantId)
            ->where('admin_id', $adminId)
            ->first();
    }

    /**
     * Count active memberships, the metric used for the staff quota.
     */
    public function activeCount(int $tenantId): int
    {
        return $this->model
            ->where('tenant_id', $tenantId)
            ->where('status', MembershipStatus::Active->value)
            ->count();
    }

    /**
     * Count staff seats, excluding the owner who is not a billed seat.
     */
    public function staffCount(int $tenantId): int
    {
        return $this->model
            ->where('tenant_id', $tenantId)
            ->where('status', MembershipStatus::Active->value)
            ->where('role', '!=', MembershipRole::Owner->value)
            ->count();
    }

    /**
     * Count the owner and every active member, for display.
     */
    public function memberCount(int $tenantId): int
    {
        return $this->model
            ->where('tenant_id', $tenantId)
            ->whereIn('status', [
                MembershipStatus::Invited->value,
                MembershipStatus::Active->value,
            ])
            ->count();
    }

    /**
     * Memberships for an admin ordered by tenant.
     */
    public function forAdmin(int $adminId)
    {
        return $this->model
            ->with('tenant')
            ->where('admin_id', $adminId)
            ->get();
    }
}
