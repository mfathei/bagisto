<?php

namespace Webkul\Saas\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Saas\Contracts\TenantEntitlement;

class TenantEntitlementRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return TenantEntitlement::class;
    }

    /**
     * Active overrides of a tenant keyed by feature.
     *
     * @return array<string, mixed>
     */
    public function overridesForTenant(int $tenantId): array
    {
        return $this->model
            ->where('tenant_id', $tenantId)
            ->where(function ($query) {
                $query->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->get()
            ->mapWithKeys(fn (TenantEntitlement $entitlement) => [
                $entitlement->feature_key => is_array($entitlement->value)
                    ? ($entitlement->value['value'] ?? null)
                    : $entitlement->value,
            ])
            ->all();
    }

    /**
     * Store an override for a feature.
     */
    public function setOverride(int $tenantId, string $featureKey, mixed $value, $expiresAt = null): TenantEntitlement
    {
        return $this->model->updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'feature_key' => $featureKey,
            ],
            [
                'value' => ['value' => $value],
                'source' => 'override',
                'expires_at' => $expiresAt,
            ]
        );
    }

    /**
     * Remove an override.
     */
    public function clearOverride(int $tenantId, string $featureKey): void
    {
        $this->model
            ->where('tenant_id', $tenantId)
            ->where('feature_key', $featureKey)
            ->delete();
    }
}
