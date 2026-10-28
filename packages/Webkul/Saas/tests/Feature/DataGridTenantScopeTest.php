<?php

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Webkul\Core\Helpers\CacheGeneration;
use Webkul\Core\Repositories\CoreConfigRepository;
use Webkul\Customer\Models\Customer;
use Webkul\GDPR\Models\GDPRDataRequest;
use Webkul\Product\Enums\SearchEngineEnum;
use Webkul\Product\Services\Search\SearchEngineManager;
use Webkul\Saas\Exceptions\CrossTenantAccessException;
use Webkul\Saas\Services\TenantContext;
use Webkul\Saas\Services\TenantManager;

use function Pest\Laravel\getJson;

/**
 * Answer the admin product grid from the database instead of Elasticsearch.
 */
function useSaasDatabaseSearchEngine(): void
{
    test()->setConfig([
        SearchEngineManager::ENABLED_KEY => '0',
        SearchEngineManager::ENGINE_KEY => SearchEngineEnum::DATABASE->value,
    ]);

    CacheGeneration::bump(CoreConfigRepository::class);
}

// ==== Admin DataGrid Isolation ====

it('lists only the customers of the active tenant', function () {
    $tenant = $this->createTenant();
    $other = $this->createTenant();

    $admin = $this->createTenantAdmin($tenant);

    $this->addMembership($tenant, $admin);

    $mine = Customer::factory()->create();
    $mine->forceFill(['tenant_id' => $tenant->getKey()])->save();

    $theirs = Customer::factory()->create();
    $theirs->forceFill(['tenant_id' => $other->getKey()])->save();

    $this->loginAsAdmin($admin);

    getJson(route('admin.customers.customers.index'), [
        'X-Requested-With' => 'XMLHttpRequest',
    ])
        ->assertOk()
        ->assertJsonCount(1, 'records')
        ->assertJsonPath('records.0.customer_id', $mine->getKey());
});

it('lists only the products of the active tenant', function () {
    useSaasDatabaseSearchEngine();

    $tenant = $this->createTenant();
    $other = $this->createTenant();

    $admin = $this->createTenantAdmin($tenant);

    $this->addMembership($tenant, $admin);

    $mine = $this->createSimpleProduct();
    $mine->forceFill(['tenant_id' => $tenant->getKey()])->save();

    $theirs = $this->createSimpleProduct();
    $theirs->forceFill(['tenant_id' => $other->getKey()])->save();

    $this->loginAsAdmin($admin);

    getJson(route('admin.catalog.products.index'), [
        'X-Requested-With' => 'XMLHttpRequest',
    ])
        ->assertOk()
        ->assertJsonCount(1, 'records')
        ->assertJsonPath('records.0.product_id', $mine->getKey());
});

// ==== Derived Record Ownership ====

it('assigns the tenant to a GDPR request created in a tenant context', function () {
    $tenant = $this->createTenant();

    $customer = Customer::factory()->create();
    $customer->forceFill(['tenant_id' => $tenant->getKey()])->save();

    $request = app(TenantContext::class)->run($tenant, fn () => GDPRDataRequest::create([
        'customer_id' => $customer->getKey(),
        'email' => $customer->email,
        'status' => 'pending',
        'type' => 'export',
        'message' => 'Send me a copy of my data.',
    ]));

    expect((int) $request->tenant_id)->toBe($tenant->getKey());
});

// ==== Tenant Boundary Errors ====

it('renders a cross-tenant write as forbidden instead of a server error', function () {
    $response = (new CrossTenantAccessException)->render(
        Request::create('/', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json'])
    );

    expect($response->getStatusCode())->toBe(403);
});

it('rejects a reserved slug when a tenant is updated', function () {
    $tenant = $this->createTenant();

    expect(fn () => app(TenantManager::class)->update($tenant, ['slug' => 'admin']))
        ->toThrow(ValidationException::class);

    expect($tenant->refresh()->slug)->not->toBe('admin');
});
