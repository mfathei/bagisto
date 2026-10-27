<?php

namespace Webkul\Saas\Services;

use Webkul\Attribute\Repositories\AttributeFamilyRepository;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Saas\Contracts\Tenant;
use Webkul\Sales\Repositories\OrderRepository;

class TenantStatistics
{
    /**
     * Create a new tenant statistics service.
     */
    public function __construct(
        protected ProductRepository $products,
        protected CategoryRepository $categories,
        protected CustomerRepository $customers,
        protected OrderRepository $orders,
        protected AttributeRepository $attributes,
        protected AttributeFamilyRepository $attributeFamilies
    ) {}

    /**
     * Commerce totals for a single tenant.
     *
     * @return array{products: int, categories: int, customers: int, orders: int, attributes: int, attribute_families: int}
     */
    public function forTenant(Tenant $tenant): array
    {
        $tenantId = $tenant->getKey();

        return [
            'products' => $this->countForTenant($this->products, $tenantId),
            'categories' => $this->countForTenant($this->categories, $tenantId),
            'customers' => $this->countForTenant($this->customers, $tenantId),
            'orders' => $this->countForTenant($this->orders, $tenantId),
            'attributes' => $this->attributes->count(),
            'attribute_families' => $this->attributeFamilies->count(),
        ];
    }

    /**
     * Commerce totals grouped by tenant for the platform overview.
     *
     * @return array<string, int>
     */
    public function platformTotals(): array
    {
        return [
            'products' => (int) $this->products->getModel()->newQuery()->count(),
            'categories' => (int) $this->categories->getModel()->newQuery()->count(),
            'customers' => (int) $this->customers->getModel()->newQuery()->count(),
            'orders' => (int) $this->orders->getModel()->newQuery()->count(),
        ];
    }

    /**
     * Count the records of a repository belonging to one tenant.
     */
    protected function countForTenant($repository, int $tenantId): int
    {
        return (int) $repository->getModel()
            ->newQuery()
            ->where('tenant_id', $tenantId)
            ->count();
    }
}
