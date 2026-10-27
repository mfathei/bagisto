<?php

namespace Webkul\Saas\Tests;

use Illuminate\Support\Str;
use Webkul\Admin\Tests\AdminTestCase;
use Webkul\Saas\Contracts\Plan;
use Webkul\Saas\Contracts\Tenant;
use Webkul\Saas\Contracts\TenantUser;
use Webkul\Saas\Enums\MembershipRole;
use Webkul\Saas\Enums\MembershipStatus;
use Webkul\Saas\Enums\TenantStatus;
use Webkul\Saas\Repositories\PlanRepository;
use Webkul\Saas\Repositories\TenantRepository;
use Webkul\Saas\Repositories\TenantUserRepository;
use Webkul\Saas\Services\TenantContext;
use Webkul\User\Contracts\Admin;
use Webkul\User\Models\Admin as AdminModel;
use Webkul\User\Models\Role;

class SaasTestCase extends AdminTestCase
{
    /**
     * Clear any context left behind by a previous test.
     */
    protected function setUp(): void
    {
        parent::setUp();

        app(TenantContext::class)->clear();
    }

    /**
     * Clear the tenant context before the application is torn down.
     */
    protected function tearDown(): void
    {
        app(TenantContext::class)->clear();

        parent::tearDown();
    }

    /**
     * Create a plan with test defaults.
     *
     * @param  array<string, mixed>  $overrides
     */
    protected function createPlan(array $overrides = []): Plan
    {
        return app(PlanRepository::class)->create(array_merge([
            'name' => 'Plan '.Str::upper(Str::random(6)),
            'slug' => 'plan-'.Str::lower(Str::random(8)),
            'description' => null,
            'price' => 10,
            'currency' => 'USD',
            'billing_interval' => 'monthly',
            'trial_days' => 14,
            'limits' => [
                'max_products' => 5,
                'max_staff' => 2,
                'monthly_orders' => 10,
                'storage_mb' => 100,
            ],
            'features' => [
                'custom_domain' => true,
                'advanced_analytics' => false,
                'api_access' => true,
                'premium_support' => false,
            ],
            'is_active' => true,
            'is_public' => true,
            'sort_order' => 0,
        ], $overrides));
    }

    /**
     * Create an active tenant with a plan attached.
     *
     * @param  array<string, mixed>  $overrides
     */
    protected function createTenant(array $overrides = []): Tenant
    {
        $planId = $overrides['plan_id'] ?? $this->createPlan()->getKey();

        return app(TenantRepository::class)->create(array_merge([
            'name' => 'Tenant '.Str::upper(Str::random(6)),
            'slug' => 'tenant-'.Str::lower(Str::random(8)),
            'email' => Str::lower(Str::random(8)).'@example.com',
            'status' => TenantStatus::Active->value,
            'plan_id' => $planId,
            'timezone' => 'UTC',
            'default_locale' => 'en',
            'default_currency' => 'USD',
            'email_verified_at' => now(),
            'approved_at' => now(),
            'activated_at' => now(),
        ], $overrides));
    }

    /**
     * Create an administrator bound to a tenant.
     *
     * @param  array<string, mixed>  $overrides
     */
    protected function createTenantAdmin(Tenant $tenant, array $overrides = []): Admin
    {
        $role = Role::factory()->create(['permission_type' => 'all', 'permissions' => null]);

        $admin = AdminModel::factory()->create(array_merge([
            'name' => 'Tenant Admin',
            'email' => Str::lower(Str::random(10)).'@example.com',
            'role_id' => $role->getKey(),
            'status' => 1,
        ], $overrides));

        $admin->forceFill(['tenant_id' => $tenant->getKey()])->save();

        return $admin;
    }

    /**
     * Add an active membership for an administrator.
     */
    protected function addMembership(
        Tenant $tenant,
        Admin $admin,
        MembershipRole $role = MembershipRole::Owner
    ): TenantUser {
        return app(TenantUserRepository::class)->create([
            'tenant_id' => $tenant->getKey(),
            'admin_id' => $admin->getKey(),
            'role' => $role->value,
            'status' => MembershipStatus::Active->value,
            'accepted_at' => now(),
            'email_verified_at' => now(),
        ]);
    }
}
