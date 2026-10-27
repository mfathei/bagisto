<?php

namespace Webkul\Saas\Services;

use Illuminate\Support\Facades\Storage;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Saas\Contracts\Tenant;
use Webkul\Saas\Enums\FeatureKey;
use Webkul\Saas\Repositories\TenantUserRepository;
use Webkul\Saas\Repositories\UsageMetricRepository;
use Webkul\Sales\Repositories\OrderRepository;

class UsageService
{
    /**
     * The number of media paths inspected when estimating storage.
     */
    protected const STORAGE_PATH_LIMIT = 5000;

    /**
     * Create a new usage service.
     */
    public function __construct(
        protected UsageMetricRepository $metrics,
        protected TenantUserRepository $memberships,
        protected ProductRepository $products,
        protected OrderRepository $orders,
        protected CustomerRepository $customers,
        protected TenantContext $context
    ) {}

    /**
     * Recalculate and persist every usage metric of a tenant.
     *
     * @return array<string, float>
     */
    public function collect(Tenant $tenant): array
    {
        $values = $this->context->run($tenant, function () use ($tenant) {
            return [
                'products' => (float) $this->products->count(),
                'orders' => $this->monthlyOrderCount(),
                'customers' => (float) $this->customers->count(),
                'staff' => (float) $this->memberships->staffCount($tenant->getKey()),
                'storage_mb' => round($this->storageBytes($tenant) / 1048576, 4),
            ];
        }, 'usage');

        foreach ($values as $metric => $value) {
            $this->metrics->store($tenant->getKey(), $metric, $value);
        }

        return $values;
    }

    /**
     * The live value of a metric without persisting it.
     */
    public function used(Tenant $tenant, string $metric): float
    {
        return match ($metric) {
            'products' => $this->context->run($tenant, fn () => (float) $this->products->count(), 'usage'),
            'orders' => $this->context->run($tenant, fn () => $this->monthlyOrderCount(), 'usage'),
            'customers' => $this->context->run($tenant, fn () => (float) $this->customers->count(), 'usage'),
            'staff' => (float) $this->memberships->staffCount($tenant->getKey()),
            'storage_mb' => round($this->storageBytes($tenant) / 1048576, 4),
            default => $this->metrics->value($tenant->getKey(), $metric),
        };
    }

    /**
     * The live usage of the metric backing a quota feature.
     */
    public function usedForFeature(Tenant $tenant, FeatureKey $feature): float
    {
        $metric = $feature->metric();

        return $metric ? $this->used($tenant, $metric) : 0.0;
    }

    /**
     * The persisted metrics of a tenant, recalculating when empty.
     *
     * @return array<string, float>
     */
    public function snapshot(Tenant $tenant): array
    {
        $stored = $this->metrics->allForTenant($tenant->getKey());

        if ($stored === []) {
            return $this->collect($tenant);
        }

        return $stored;
    }

    /**
     * Orders placed in the current calendar month.
     */
    protected function monthlyOrderCount(): float
    {
        return (float) $this->orders
            ->scopeQuery(fn ($query) => $query->where('created_at', '>=', now()->startOfMonth()))
            ->count();
    }

    /**
     * Estimate the media footprint of a tenant in bytes.
     */
    protected function storageBytes(Tenant $tenant): float
    {
        $disk = Storage::disk('public');

        $paths = $this->products
            ->scopeQuery(function ($query) {
                return $query
                    ->join('product_images', 'product_images.product_id', '=', 'products.id')
                    ->select('product_images.path')
                    ->limit(self::STORAGE_PATH_LIMIT);
            })
            ->get();

        $bytes = 0.0;

        foreach ($paths as $product) {
            $path = $product->path;

            if (! $path) {
                continue;
            }

            try {
                if ($disk->exists($path)) {
                    $bytes += (float) $disk->size($path);
                }
            } catch (\Throwable) {
                continue;
            }
        }

        return $bytes;
    }
}
