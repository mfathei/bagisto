<?php

namespace Webkul\Saas\Http\Controllers\SuperAdmin;

use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Webkul\Customer\Repositories\CustomerRepository;
use Webkul\Product\Repositories\ProductFlatRepository;
use Webkul\Saas\Http\Controllers\Controller;
use Webkul\Saas\Repositories\TenantRepository;
use Webkul\Sales\Repositories\OrderRepository;

class TenantDataController extends Controller
{
    /**
     * Create a new controller instance.
     */
    public function __construct(
        protected TenantRepository $tenants,
        protected ProductFlatRepository $productFlats,
        protected CustomerRepository $customers,
        protected OrderRepository $orders
    ) {}

    /**
     * List products sold by every tenant.
     */
    public function products(Request $request)
    {
        $tenantId = $request->integer('tenant_id') ?: null;

        $search = trim((string) $request->input('search'));

        $products = $this->productFlats
            ->scopeQuery(function ($query) use ($tenantId, $search) {
                $query->join('products', 'products.id', '=', 'product_flat.product_id')
                    ->select('product_flat.*', 'products.tenant_id as tenant_id')
                    ->where('product_flat.locale', app()->getLocale())
                    ->where('product_flat.channel', core()->getDefaultChannelCode())
                    ->when($tenantId, fn ($query) => $query->where('products.tenant_id', $tenantId))
                    ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                        $term = '%'.$search.'%';

                        $query->where('product_flat.name', 'like', $term)
                            ->orWhere('product_flat.sku', 'like', $term);
                    }))
                    ->orderByDesc('product_flat.product_id');

                return $query;
            })
            ->paginate(15)
            ->withQueryString();

        return view('saas::super-admin.tenants.products', $this->sharedData($request, [
            'products' => $products,
            'search' => $search,
            'tenantId' => $tenantId,
        ]));
    }

    /**
     * List customers registered on every storefront.
     */
    public function customers(Request $request)
    {
        $tenantId = $request->integer('tenant_id') ?: null;

        $search = trim((string) $request->input('search'));

        $customers = $this->customers
            ->scopeQuery(function ($query) use ($tenantId, $search) {
                $query->with(['group'])
                    ->withCount('orders')
                    ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
                    ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                        $term = '%'.$search.'%';

                        $query->where('email', 'like', $term)
                            ->orWhere('first_name', 'like', $term)
                            ->orWhere('last_name', 'like', $term)
                            ->orWhere('phone', 'like', $term);
                    }))
                    ->orderByDesc('id');

                return $query;
            })
            ->paginate(15)
            ->withQueryString();

        return view('saas::super-admin.tenants.customers', $this->sharedData($request, [
            'customers' => $customers,
            'search' => $search,
            'tenantId' => $tenantId,
        ]));
    }

    /**
     * List orders placed on every storefront.
     */
    public function orders(Request $request)
    {
        $tenantId = $request->integer('tenant_id') ?: null;

        $search = trim((string) $request->input('search'));

        $status = $request->input('status');

        $orders = $this->orders
            ->scopeQuery(function ($query) use ($tenantId, $search, $status) {
                $query->with(['payment', 'addresses'])
                    ->withCount('items')
                    ->when($tenantId, fn ($query) => $query->where('tenant_id', $tenantId))
                    ->when($status, fn ($query) => $query->where('status', $status))
                    ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                        $term = '%'.$search.'%';

                        $query->where('increment_id', 'like', $term)
                            ->orWhere('customer_email', 'like', $term)
                            ->orWhere('customer_first_name', 'like', $term)
                            ->orWhere('customer_last_name', 'like', $term);
                    }))
                    ->orderByDesc('id');

                return $query;
            })
            ->paginate(15)
            ->withQueryString();

        return view('saas::super-admin.tenants.orders', $this->sharedData($request, [
            'orders' => $orders,
            'search' => $search,
            'tenantId' => $tenantId,
            'status' => $status,
        ]));
    }

    /**
     * Data shared by the three cross-tenant listings.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function sharedData(Request $request, array $data): array
    {
        return array_merge($data, [
            'tenantDirectory' => $this->tenantDirectory(),
            'tenantOptions' => $this->tenants->getModel()->newQuery()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    /**
     * The primary domain of every tenant, keyed by tenant identifier.
     *
     * @return Collection<int, string>
     */
    protected function tenantDirectory()
    {
        return $this->tenants->getModel()
            ->newQuery()
            ->with(['domains'])
            ->get()
            ->mapWithKeys(function ($tenant) {
                $domain = $tenant->domains->firstWhere('is_primary', true)
                    ?: $tenant->domains->firstWhere('status', 'verified');

                return [$tenant->getKey() => $domain?->hostname ?? $tenant->slug];
            });
    }
}
