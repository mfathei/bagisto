<?php

use Illuminate\Support\Str;
use Webkul\Saas\Enums\DomainStatus;
use Webkul\Saas\Enums\TenantStatus;
use Webkul\Saas\Models\SuperAdmin;
use Webkul\Saas\Policies\TenantPolicy;
use Webkul\Saas\Services\DomainService;
use Webkul\User\Models\Admin as AdminModel;
use Webkul\User\Models\Role;

// ==== Panel Boundary ====

it('redirects a tenant administrator away from the platform panel', function () {
    $tenant = $this->createTenant();
    $admin = $this->createTenantAdmin($tenant);

    $this->addMembership($tenant, $admin);

    $this->actingAs($admin, 'admin')
        ->get(route('saas.super-admin.dashboard'))
        ->assertRedirect(route('saas.super-admin.login'));

    $this->assertGuest('super_admin');
});

it('denies the tenant panel to an administrator without a tenant', function () {
    $role = Role::factory()->create(['permission_type' => 'all', 'permissions' => null]);

    $admin = AdminModel::factory()->create([
        'name' => 'Platform Admin',
        'email' => Str::lower(Str::random(10)).'@example.com',
        'role_id' => $role->getKey(),
        'status' => 1,
    ]);

    $this->actingAs($admin, 'admin')
        ->get(route('saas.tenant.dashboard'))
        ->assertForbidden();
});

it('blocks the tenant panel of a suspended tenant and ends the session', function () {
    $tenant = $this->createTenant([
        'status' => TenantStatus::Suspended->value,
        'suspended_at' => now(),
        'suspension_reason' => 'Billing hold',
    ]);

    $admin = $this->createTenantAdmin($tenant);

    $this->addMembership($tenant, $admin);

    $this->actingAs($admin, 'admin')
        ->get(route('saas.tenant.dashboard'))
        ->assertForbidden();

    $this->assertGuest('admin');
});

// ==== Cross-Tenant Requests ====

it('returns not found when an administrator verifies another tenant domain', function () {
    $owner = $this->createTenant();
    $other = $this->createTenant();

    $domain = app(DomainService::class)->addCustomDomain($other, 'other-'.Str::lower(Str::random(6)).'.example.com');

    $admin = $this->createTenantAdmin($owner);

    $this->addMembership($owner, $admin);

    $this->actingAs($admin, 'admin')
        ->post(route('saas.tenant.domains.verify', $domain->getKey()))
        ->assertNotFound();

    expect($domain->refresh()->status)->toBe(DomainStatus::Pending)
        ->and($domain->verified_at)->toBeNull();
});

it('returns not found when an administrator changes another tenant primary domain', function () {
    $owner = $this->createTenant();
    $other = $this->createTenant();

    $domain = app(DomainService::class)->registerPlatformDomain($other);

    $admin = $this->createTenantAdmin($owner);

    $this->addMembership($owner, $admin);

    $this->actingAs($admin, 'admin')
        ->post(route('saas.tenant.domains.primary', $domain->getKey()))
        ->assertNotFound();

    expect((int) $domain->refresh()->tenant_id)->toBe($other->getKey());
});

// ==== Host Resolution ====

it('returns not found for a host that belongs to no tenant', function () {
    $this->createTenant();

    $this->get('http://unknown-'.Str::lower(Str::random(8)).'.example.com/')
        ->assertNotFound();
});

it('serves the storefront of a verified tenant host', function () {
    $tenant = $this->createTenant();

    $domain = app(DomainService::class)->registerPlatformDomain($tenant);

    expect($domain->status->value)->toBe(DomainStatus::Verified->value);

    $this->get('http://'.$domain->hostname.'/')->assertOk();
});

it('returns service unavailable for the host of a suspended tenant', function () {
    $tenant = $this->createTenant([
        'status' => TenantStatus::Suspended->value,
        'suspended_at' => now(),
        'suspension_reason' => 'Billing hold',
    ]);

    $domain = app(DomainService::class)->registerPlatformDomain($tenant);

    $this->get('http://'.$domain->hostname.'/')->assertStatus(503);
});

// ==== Api Boundary ====

it('rejects api requests without a token', function () {
    $this->getJson('/api/v1/saas/tenant')->assertUnauthorized();
});

it('resolves the api tenant from the token', function () {
    $tenant = $this->createTenant();
    $admin = $this->createTenantAdmin($tenant);

    $this->addMembership($tenant, $admin);

    $token = $admin->createToken('platform-security-test')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/saas/tenant')
        ->assertOk()
        ->assertJsonPath('data.uuid', $tenant->uuid);
});

it('rejects api tokens that are not attached to a tenant', function () {
    $role = Role::factory()->create(['permission_type' => 'all', 'permissions' => null]);

    $admin = AdminModel::factory()->create([
        'name' => 'Token Admin',
        'email' => Str::lower(Str::random(10)).'@example.com',
        'role_id' => $role->getKey(),
        'status' => 1,
    ]);

    $token = $admin->createToken('platform-security-test')->plainTextToken;

    $this->withHeader('Authorization', 'Bearer '.$token)
        ->getJson('/api/v1/saas/tenant')
        ->assertForbidden();
});

// ==== Policy Boundaries ====

it('denies a tenant administrator the platform tenant listing', function () {
    $tenant = $this->createTenant();
    $admin = $this->createTenantAdmin($tenant);

    expect((new TenantPolicy)->viewAny($admin))->toBeFalse();
});

it('allows a super admin to list tenants', function () {
    $superAdmin = SuperAdmin::create([
        'name' => 'Platform Owner',
        'email' => Str::lower(Str::random(10)).'@example.com',
        'password' => 'secret-password',
        'role' => 'owner',
        'status' => true,
    ]);

    expect((new TenantPolicy)->viewAny($superAdmin))->toBeTrue();
});

it('allows a support super admin to read tenants but not manage them', function () {
    $superAdmin = SuperAdmin::create([
        'name' => 'Support Agent',
        'email' => Str::lower(Str::random(10)).'@example.com',
        'password' => 'secret-password',
        'role' => 'support',
        'status' => true,
    ]);

    $tenant = $this->createTenant();
    $policy = new TenantPolicy;

    expect($policy->viewAny($superAdmin))->toBeTrue()
        ->and($policy->update($superAdmin, $tenant))->toBeFalse()
        ->and($policy->delete($superAdmin, $tenant))->toBeFalse();
});
