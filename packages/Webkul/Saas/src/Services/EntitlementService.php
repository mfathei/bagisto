<?php

namespace Webkul\Saas\Services;

use Webkul\Saas\Contracts\Tenant;
use Webkul\Saas\Enums\FeatureKey;
use Webkul\Saas\Exceptions\FeatureNotAvailableException;
use Webkul\Saas\Exceptions\QuotaExceededException;
use Webkul\Saas\Repositories\TenantEntitlementRepository;

class EntitlementService
{
    /**
     * Create a new entitlement service.
     */
    public function __construct(
        protected TenantEntitlementRepository $overrides,
        protected UsageService $usage,
        protected PlatformSettings $settings
    ) {}

    /**
     * The numeric limit of a quota feature, null meaning unlimited.
     */
    public function limit(Tenant $tenant, FeatureKey $feature): ?int
    {
        $overrides = $this->overrides->overridesForTenant($tenant->getKey());

        if (array_key_exists($feature->value, $overrides)) {
            $value = $overrides[$feature->value];

            return $value === null || $value === '' ? null : (int) $value;
        }

        $plan = $tenant->plan;

        if ($plan) {
            return $plan->limit($feature);
        }

        return match ($feature) {
            FeatureKey::MaxProducts => $this->settings->integer('default_product_limit', 100),
            FeatureKey::MaxStaff => $this->settings->integer('default_staff_limit', 5),
            default => null,
        };
    }

    /**
     * Whether a feature is available to the tenant at all.
     */
    public function includes(Tenant $tenant, FeatureKey $feature): bool
    {
        $overrides = $this->overrides->overridesForTenant($tenant->getKey());

        if (array_key_exists($feature->value, $overrides)) {
            return (bool) $overrides[$feature->value];
        }

        if ($feature->isQuota()) {
            return $this->limit($tenant, $feature) !== 0;
        }

        return (bool) $tenant->plan?->includes($feature);
    }

    /**
     * Whether the tenant may create more of a quota-limited resource.
     */
    public function canCreate(Tenant $tenant, FeatureKey $feature, float $amount = 1): bool
    {
        $limit = $this->limit($tenant, $feature);

        if ($limit === null) {
            return true;
        }

        $allowed = $this->effectiveLimit($limit);

        return $this->usage->usedForFeature($tenant, $feature) + $amount <= $allowed;
    }

    /**
     * Fail unless the tenant may create more of a quota-limited resource.
     */
    public function assertCanCreate(Tenant $tenant, FeatureKey $feature, float $amount = 1): void
    {
        if ($this->canCreate($tenant, $feature, $amount)) {
            return;
        }

        throw new QuotaExceededException(
            $feature->value,
            $this->limit($tenant, $feature),
            $this->usage->usedForFeature($tenant, $feature),
            trans('saas::app.errors.quota-exceeded', [
                'feature' => $feature->label(),
                'limit' => $this->limit($tenant, $feature),
            ])
        );
    }

    /**
     * Fail unless a boolean feature is included in the tenant plan.
     */
    public function assertFeature(Tenant $tenant, FeatureKey $feature): void
    {
        if (! $this->includes($tenant, $feature)) {
            throw new FeatureNotAvailableException($feature->label());
        }
    }

    /**
     * A human readable usage summary for dashboards and settings pages.
     *
     * @return array<int, array<string, mixed>>
     */
    public function summary(Tenant $tenant): array
    {
        $rows = [];

        foreach (FeatureKey::cases() as $feature) {
            if ($feature->isQuota()) {
                $limit = $this->limit($tenant, $feature);
                $used = $this->usage->usedForFeature($tenant, $feature);

                $rows[] = [
                    'key' => $feature->value,
                    'label' => $feature->label(),
                    'type' => 'quota',
                    'limit' => $limit,
                    'used' => $used,
                    'remaining' => $limit === null ? null : max(0, $limit - $used),
                    'percent' => $limit ? min(100, round($used / max(1, $limit) * 100, 1)) : null,
                    'over' => $limit !== null && $used > $limit,
                    'included' => $limit !== 0,
                ];

                continue;
            }

            $rows[] = [
                'key' => $feature->value,
                'label' => $feature->label(),
                'type' => 'feature',
                'limit' => null,
                'used' => null,
                'remaining' => null,
                'percent' => null,
                'over' => false,
                'included' => $this->includes($tenant, $feature),
            ];
        }

        return $rows;
    }

    /**
     * Apply the configured overage tolerance to a base limit.
     */
    protected function effectiveLimit(int $limit): float
    {
        $percent = max(0, (int) config('saas.entitlements.grace_overage_percent', 0));

        return $percent > 0 ? $limit + floor($limit * $percent / 100) : $limit;
    }
}
