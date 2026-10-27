<?php

namespace Webkul\Saas\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Saas\Contracts\TenantDomain;
use Webkul\Saas\Enums\DomainStatus;

class TenantDomainRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return TenantDomain::class;
    }

    /**
     * Find a domain by its exact, normalised hostname.
     */
    public function findByHost(string $hostname): ?TenantDomain
    {
        return $this->model
            ->with('tenant')
            ->where('hostname', $hostname)
            ->first();
    }

    /**
     * The primary domain of a tenant.
     */
    public function primaryForTenant(int $tenantId): ?TenantDomain
    {
        return $this->model
            ->where('tenant_id', $tenantId)
            ->orderByDesc('is_primary')
            ->orderByRaw('case when status = ? then 0 else 1 end', [DomainStatus::Verified->value])
            ->first();
    }

    /**
     * Custom domains with a pending verification.
     */
    public function pendingCustomDomains()
    {
        return $this->model
            ->where('type', 'custom')
            ->where('status', DomainStatus::Pending->value)
            ->get();
    }

    /**
     * Whether a hostname is already assigned anywhere on the platform.
     */
    public function hostnameTaken(string $hostname, ?int $exceptId = null): bool
    {
        return $this->model
            ->where('hostname', $hostname)
            ->when($exceptId, fn ($query) => $query->whereKeyNot($exceptId))
            ->exists();
    }
}
