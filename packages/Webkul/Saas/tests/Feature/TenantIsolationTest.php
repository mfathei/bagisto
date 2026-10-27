<?php

use Webkul\Saas\Exceptions\CrossTenantAccessException;
use Webkul\Saas\Services\TenantContext;
use Webkul\User\Models\Admin;
use Webkul\User\Models\Role;

// ==== Automatic Ownership ====

it('assigns the active tenant to a newly created record', function () {
    $tenant = $this->createTenant();
    $role = Role::factory()->create(['permission_type' => 'all']);

    $admin = app(TenantContext::class)->run($tenant, fn () => Admin::create([
        'name' => 'Scoped Admin',
        'email' => 'scoped-admin@example.com',
        'password' => bcrypt('secret-password'),
        'role_id' => $role->getKey(),
        'status' => 1,
    ]));

    expect((int) $admin->tenant_id)->toBe($tenant->getKey());
});

it('rejects a record that names another tenant', function () {
    $tenant = $this->createTenant();
    $other = $this->createTenant();
    $role = Role::factory()->create(['permission_type' => 'all']);

    expect(fn () => app(TenantContext::class)->run($tenant, function () use ($other, $role) {
        $admin = new Admin([
            'name' => 'Forged Admin',
            'email' => 'forged-admin@example.com',
            'password' => bcrypt('secret-password'),
            'role_id' => $role->getKey(),
            'status' => 1,
        ]);

        $admin->tenant_id = $other->getKey();

        $admin->save();
    }))->toThrow(CrossTenantAccessException::class);
});

// ==== Query Isolation ====

it('does not return another tenant records', function () {
    $tenant = $this->createTenant();
    $other = $this->createTenant();
    $role = Role::factory()->create(['permission_type' => 'all']);
    $context = app(TenantContext::class);

    $context->run($tenant, fn () => Admin::create([
        'name' => 'Tenant One Admin',
        'email' => 'tenant-one-admin@example.com',
        'password' => bcrypt('secret-password'),
        'role_id' => $role->getKey(),
        'status' => 1,
    ]));

    $context->clear();

    expect(Admin::where('email', 'tenant-one-admin@example.com')->exists())->toBeTrue();

    $visibleToOther = $context->run(
        $other,
        fn () => Admin::where('email', 'tenant-one-admin@example.com')->exists()
    );

    expect($visibleToOther)->toBeFalse();
});

it('refuses to update a record owned by another tenant', function () {
    $tenant = $this->createTenant();
    $other = $this->createTenant();
    $role = Role::factory()->create(['permission_type' => 'all']);
    $context = app(TenantContext::class);

    $admin = $context->run($tenant, fn () => Admin::create([
        'name' => 'Protected Admin',
        'email' => 'protected-admin@example.com',
        'password' => bcrypt('secret-password'),
        'role_id' => $role->getKey(),
        'status' => 1,
    ]));

    $context->clear();

    expect(fn () => $context->run($other, fn () => $admin->forceFill(['name' => 'Hijacked'])->save()))
        ->toThrow(CrossTenantAccessException::class);

    expect(Admin::withoutGlobalScopes()->find($admin->getKey())->name)->toBe('Protected Admin');
});
