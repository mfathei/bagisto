<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Webkul\Saas\Enums\ProvisioningStatus;
use Webkul\Saas\Enums\TenantStatus;
use Webkul\Saas\Exceptions\ProvisioningFailedException;
use Webkul\Saas\Repositories\ProvisioningOperationRepository;
use Webkul\Saas\Services\DomainService;
use Webkul\Saas\Services\PlatformSettings;
use Webkul\Saas\Services\ProvisioningService;
use Webkul\Saas\Services\SubscriptionService;
use Webkul\Saas\Services\TenantManager;

// ==== Successful Provisioning ====

it('provisions a verified tenant end to end', function () {
    $plan = $this->createPlan(['trial_days' => 14]);

    $result = app(TenantManager::class)->create([
        'name' => 'Acme Store',
        'slug' => 'acme-'.Str::lower(Str::random(6)),
        'owner_name' => 'Acme Owner',
        'owner_email' => 'owner-'.Str::lower(Str::random(6)).'@example.com',
        'owner_password' => 'correct-horse-battery',
        'plan_id' => $plan->getKey(),
        'email_verified' => true,
        'approved' => true,
    ]);

    $tenant = $result['tenant'];

    expect($tenant->status)->toBe(TenantStatus::Pending);

    $operation = app(ProvisioningService::class)->provision($tenant);

    $tenant->refresh();

    expect($operation->status)->toBe(ProvisioningStatus::Completed)
        ->and($operation->step)->toBe('active')
        ->and($tenant->status)->toBe(TenantStatus::Active)
        ->and($tenant->activated_at)->not->toBeNull()
        ->and($tenant->channel_id)->not->toBeNull()
        ->and($tenant->owner_admin_id)->toBe($result['owner']->getKey())
        ->and($tenant->settings['onboarding']['published'])->toBeFalse();

    $channel = DB::table('channels')->where('code', 'tenant-'.$tenant->getKey())->first();
    $domain = $tenant->domains()->where('is_primary', true)->first();
    $subscription = app(SubscriptionService::class)->current($tenant);

    expect($channel)->not->toBeNull()
        ->and($channel->hostname)->toBe($tenant->slug.'.'.config('saas.domains.suffix'))
        ->and($domain->hostname)->toBe($channel->hostname)
        ->and(DB::table('inventory_sources')->where('code', 't'.$tenant->getKey().'-main')->exists())->toBeTrue()
        ->and(DB::table('category_translations')->where('slug', 't'.$tenant->getKey().'-root')->exists())->toBeTrue()
        ->and($subscription)->not->toBeNull()
        ->and((int) $subscription->plan_id)->toBe($plan->getKey())
        ->and((int) $tenant->plan_id)->toBe($plan->getKey());
});

it('does not repeat work when provisioning is invoked again', function () {
    $result = app(TenantManager::class)->create([
        'name' => 'Idempotent Store',
        'slug' => 'idem-'.Str::lower(Str::random(6)),
        'owner_name' => 'Idempotent Owner',
        'owner_email' => 'idem-'.Str::lower(Str::random(6)).'@example.com',
        'owner_password' => 'correct-horse-battery',
        'plan_id' => $this->createPlan(['trial_days' => 0])->getKey(),
        'email_verified' => true,
        'approved' => true,
    ]);

    $tenant = $result['tenant'];

    app(ProvisioningService::class)->provision($tenant);
    $tenant->refresh();

    $again = app(ProvisioningService::class)->provision($tenant);

    expect($again->status)->toBe(ProvisioningStatus::Completed)
        ->and($again->attempts)->toBe(1)
        ->and($tenant->domains()->count())->toBe(1)
        ->and(DB::table('channels')->where('code', 'tenant-'.$tenant->getKey())->count())->toBe(1)
        ->and(DB::table('saas_provisioning_operations')->where('tenant_id', $tenant->getKey())->count())->toBe(1)
        ->and(DB::table('saas_subscriptions')->where('tenant_id', $tenant->getKey())->count())->toBe(1);
});

// ==== Activation Requirements ====

it('leaves an unverified tenant pending instead of activating it', function () {
    $settings = app(PlatformSettings::class);
    $settings->set('require_email_verification', true, 'boolean');
    $settings->set('require_approval', true, 'boolean');

    $result = app(TenantManager::class)->create([
        'name' => 'Unverified Store',
        'slug' => 'unverified-'.Str::lower(Str::random(6)),
        'owner_name' => 'Unverified Owner',
        'owner_email' => 'pending-'.Str::lower(Str::random(6)).'@example.com',
        'owner_password' => 'correct-horse-battery',
        'plan_id' => $this->createPlan()->getKey(),
    ]);

    $tenant = $result['tenant'];

    $operation = app(ProvisioningService::class)->provision($tenant);

    $tenant->refresh();

    expect($operation->status)->toBe(ProvisioningStatus::Completed)
        ->and($operation->step)->toBe('awaiting_requirements')
        ->and($tenant->status)->toBe(TenantStatus::Pending)
        ->and($tenant->activated_at)->toBeNull()
        ->and(DB::table('saas_subscriptions')->where('tenant_id', $tenant->getKey())->exists())->toBeFalse();
});

// ==== Failure Handling ====

it('rolls back provisioning and records a failed operation', function () {
    $tenant = app(TenantManager::class)->create([
        'name' => 'Failing Store',
        'slug' => 'failing-'.Str::lower(Str::random(6)),
        'owner_name' => 'Failing Owner',
        'owner_email' => 'failing-'.Str::lower(Str::random(6)).'@example.com',
        'owner_password' => 'correct-horse-battery',
        'plan_id' => $this->createPlan()->getKey(),
        'email_verified' => true,
        'approved' => true,
    ])['tenant'];

    $this->partialMock(DomainService::class, function ($mock) {
        $mock->shouldReceive('registerPlatformDomain')
            ->once()
            ->andThrow(new RuntimeException('DNS service unavailable'));
    });

    $failed = null;

    try {
        app(ProvisioningService::class)->provision($tenant);
    } catch (ProvisioningFailedException $exception) {
        $failed = $exception;
    }

    expect($failed)->not->toBeNull()
        ->and($failed->step)->toBe('domain');

    $tenant->refresh();
    $operation = app(ProvisioningOperationRepository::class)->latestForTenant($tenant->getKey());

    expect($tenant->status)->toBe(TenantStatus::Pending)
        ->and($tenant->activated_at)->toBeNull()
        ->and($operation->status)->toBe(ProvisioningStatus::Failed)
        ->and($operation->step)->toBe('domain')
        ->and($operation->error)->toContain('DNS service unavailable')
        ->and($tenant->domains()->count())->toBe(0)
        ->and(DB::table('channels')->where('code', 'tenant-'.$tenant->getKey())->exists())->toBeFalse()
        ->and(DB::table('inventory_sources')->where('code', 't'.$tenant->getKey().'-main')->exists())->toBeFalse()
        ->and(DB::table('category_translations')->where('slug', 't'.$tenant->getKey().'-root')->exists())->toBeFalse()
        ->and(DB::table('saas_subscriptions')->where('tenant_id', $tenant->getKey())->exists())->toBeFalse();
});
