<?php

namespace Webkul\Saas\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Saas\Contracts\UsageMetric;

class UsageMetricRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return UsageMetric::class;
    }

    /**
     * Current value of a metric for a tenant.
     */
    public function value(int $tenantId, string $metric): float
    {
        return (float) $this->model
            ->where('tenant_id', $tenantId)
            ->where('metric', $metric)
            ->where('period', 'current')
            ->value('value');
    }

    /**
     * Store or update the current value of a metric.
     */
    public function store(int $tenantId, string $metric, float $value, array $metadata = []): UsageMetric
    {
        return $this->model->updateOrCreate(
            [
                'tenant_id' => $tenantId,
                'metric' => $metric,
                'period' => 'current',
            ],
            [
                'value' => $value,
                'metadata' => $metadata,
            ]
        );
    }

    /**
     * All current metrics of a tenant keyed by metric name.
     *
     * @return array<string, float>
     */
    public function allForTenant(int $tenantId): array
    {
        return $this->model
            ->where('tenant_id', $tenantId)
            ->where('period', 'current')
            ->pluck('value', 'metric')
            ->map(fn ($value) => (float) $value)
            ->all();
    }
}
