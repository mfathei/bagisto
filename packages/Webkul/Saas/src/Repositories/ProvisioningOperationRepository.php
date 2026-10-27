<?php

namespace Webkul\Saas\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Saas\Contracts\ProvisioningOperation;

class ProvisioningOperationRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return ProvisioningOperation::class;
    }

    /**
     * Find an operation by its idempotency key.
     */
    public function findByIdempotencyKey(string $key): ?ProvisioningOperation
    {
        return $this->model->where('idempotency_key', $key)->first();
    }

    /**
     * The latest provisioning operation of a tenant.
     */
    public function latestForTenant(int $tenantId): ?ProvisioningOperation
    {
        return $this->model
            ->where('tenant_id', $tenantId)
            ->orderByDesc('id')
            ->first();
    }

    /**
     * Failed operations requiring attention.
     */
    public function failed()
    {
        return $this->model
            ->with('tenant')
            ->where('status', 'failed')
            ->orderByDesc('created_at')
            ->get();
    }
}
