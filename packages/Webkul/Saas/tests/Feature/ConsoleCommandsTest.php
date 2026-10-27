<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Webkul\Saas\Enums\SubscriptionStatus;
use Webkul\Saas\Enums\TenantStatus;
use Webkul\Saas\Mail\TrialEndingMail;
use Webkul\Saas\Repositories\SuperAdminRepository;
use Webkul\Saas\Services\SubscriptionService;
use Webkul\Saas\Services\TenantManager;

// ==== Installation ====

it('seeds plans and settings without duplicating them', function () {
    $this->artisan('saas:install')->assertSuccessful();

    $planCount = DB::table('saas_plans')->count();
    $settingCount = DB::table('saas_platform_settings')->count();

    $this->artisan('saas:install')->assertSuccessful();

    expect(DB::table('saas_plans')->count())->toBe($planCount)
        ->and(DB::table('saas_platform_settings')->count())->toBe($settingCount)
        ->and(DB::table('saas_plans')->where('slug', 'free')->exists())->toBeTrue()
        ->and(DB::table('saas_platform_settings')->where('key', 'platform_name')->exists())->toBeTrue();
});

// ==== Super Admin Bootstrap ====

it('creates a super admin without a default password and keeps it out of the audit log', function () {
    $email = 'root-'.Str::lower(Str::random(6)).'@example.com';
    $password = 'super-secret-password';

    $this->artisan('saas:create-super-admin')
        ->expectsQuestion('Super Admin name', 'Root Admin')
        ->expectsQuestion('Email address', $email)
        ->expectsQuestion('Password (at least 12 characters)', $password)
        ->expectsQuestion('Confirm password', $password)
        ->assertSuccessful();

    $admin = app(SuperAdminRepository::class)->findByEmail($email);
    $audit = DB::table('saas_audit_logs')->where('action', 'super_admin.created')->latest('id')->first();

    expect($admin)->not->toBeNull()
        ->and($admin->role)->toBe('owner')
        ->and(Hash::check($password, $admin->password))->toBeTrue()
        ->and($audit)->not->toBeNull()
        ->and($audit->metadata)->not->toContain($password)
        ->and($audit->metadata)->not->toContain('password');
});

it('refuses to create a second super admin with the same email', function () {
    $email = 'root-'.Str::lower(Str::random(6)).'@example.com';
    $password = 'super-secret-password';

    $this->artisan('saas:create-super-admin')
        ->expectsQuestion('Super Admin name', 'Root Admin')
        ->expectsQuestion('Email address', $email)
        ->expectsQuestion('Password (at least 12 characters)', $password)
        ->expectsQuestion('Confirm password', $password)
        ->assertSuccessful();

    $this->artisan('saas:create-super-admin')
        ->expectsQuestion('Super Admin name', 'Root Admin')
        ->expectsQuestion('Email address', $email)
        ->assertFailed();
});

// ==== Trial Warnings ====

it('queues each trial warning once per threshold', function () {
    $tenant = $this->createTenant();
    $subscription = app(SubscriptionService::class)->start($tenant, $this->createPlan(['trial_days' => 14]), 14);

    $subscription->forceFill(['trial_ends_at' => now()->addDays(3)])->save();

    Mail::fake();

    $this->artisan('saas:send-trial-warnings')->assertSuccessful();
    $this->artisan('saas:send-trial-warnings')->assertSuccessful();

    expect(Mail::queued(TrialEndingMail::class))->toHaveCount(1)
        ->and($subscription->refresh()->metadata['trial_warnings'])->toBe([3, 7]);
});

// ==== Usage Collection ====

it('collects usage metrics for a single tenant', function () {
    $tenant = $this->createTenant();

    $this->artisan('saas:collect-usage', ['--tenant' => $tenant->getKey()])->assertSuccessful();

    $metrics = DB::table('saas_usage_metrics')
        ->where('tenant_id', $tenant->getKey())
        ->pluck('metric')
        ->all();

    expect($metrics)->toContain('products', 'orders', 'customers', 'staff', 'storage_mb');
});

it('fails usage collection for an unknown tenant', function () {
    $this->artisan('saas:collect-usage', ['--tenant' => 'missing-tenant'])->assertFailed();
});

// ==== Lifecycle And Provisioning ====

it('moves an expired trial past due through the sync command', function () {
    $tenant = $this->createTenant();
    $subscription = app(SubscriptionService::class)->start($tenant, $this->createPlan(['trial_days' => 14]), 14);

    $subscription->forceFill(['trial_ends_at' => now()->subDay()])->save();

    $this->artisan('saas:sync-subscriptions')->assertSuccessful();

    expect($subscription->refresh()->status)->toBe(SubscriptionStatus::PastDue);
});

it('provisions a pending tenant synchronously', function () {
    $tenant = app(TenantManager::class)->create([
        'name' => 'Console Store',
        'slug' => 'console-'.Str::lower(Str::random(6)),
        'owner_name' => 'Console Owner',
        'owner_email' => 'console-'.Str::lower(Str::random(6)).'@example.com',
        'owner_password' => 'correct-horse-battery',
        'plan_id' => $this->createPlan()->getKey(),
        'email_verified' => true,
        'approved' => true,
    ])['tenant'];

    $this->artisan('saas:provision-tenant', [
        'tenant' => $tenant->getKey(),
        '--sync' => true,
    ])->assertSuccessful();

    expect($tenant->refresh()->status)->toBe(TenantStatus::Active);
});
