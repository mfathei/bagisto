<?php

use Illuminate\Validation\ValidationException;
use Webkul\Saas\Contracts\DomainChallenge as DomainChallengeContract;
use Webkul\Saas\Contracts\DomainOwnershipVerifier;
use Webkul\Saas\Enums\DomainStatus;
use Webkul\Saas\Enums\DomainType;
use Webkul\Saas\Enums\SslStatus;
use Webkul\Saas\Exceptions\CrossTenantAccessException;
use Webkul\Saas\Exceptions\FeatureNotAvailableException;
use Webkul\Saas\Repositories\DomainChallengeRepository;
use Webkul\Saas\Services\DomainService;

// ==== Platform Subdomains ====

it('registers the platform subdomain as verified with pending ssl', function () {
    $tenant = $this->createTenant();

    $domain = app(DomainService::class)->registerPlatformDomain($tenant);

    expect($domain->type)->toBe(DomainType::PlatformSubdomain)
        ->and($domain->status)->toBe(DomainStatus::Verified)
        ->and($domain->ssl_status)->toBe(SslStatus::Pending)
        ->and($domain->is_primary)->toBeTrue()
        ->and($domain->hostname)->toBe($tenant->slug.'.'.config('saas.domains.suffix'));
});

it('keeps a single platform domain when registration is repeated', function () {
    $tenant = $this->createTenant();
    $service = app(DomainService::class);

    $service->registerPlatformDomain($tenant);
    $service->registerPlatformDomain($tenant);

    expect($tenant->domains()->where('type', DomainType::PlatformSubdomain->value)->count())->toBe(1);
});

// ==== Custom Domains ====

it('adds a custom domain pending verification with a dns challenge', function () {
    $tenant = $this->createTenant();

    $domain = app(DomainService::class)->addCustomDomain($tenant, 'store.example.com');

    $challenge = app(DomainChallengeRepository::class)->pendingForDomain($domain->getKey());

    expect($domain->status)->toBe(DomainStatus::Pending)
        ->and($domain->ssl_status)->toBe(SslStatus::Pending)
        ->and($domain->is_primary)->toBeFalse()
        ->and($challenge)->not->toBeNull()
        ->and($challenge->expected_value)->toStartWith('saas-verification=');
});

it('rejects a hostname on the platform suffix', function () {
    $tenant = $this->createTenant();

    expect(fn () => app(DomainService::class)->addCustomDomain($tenant, 'taken.'.config('saas.domains.suffix')))
        ->toThrow(ValidationException::class);
});

it('rejects a hostname already assigned to another tenant', function () {
    $first = $this->createTenant();
    $second = $this->createTenant();
    $service = app(DomainService::class);

    $service->addCustomDomain($first, 'shared.example.com');

    expect(fn () => $service->addCustomDomain($second, 'shared.example.com'))
        ->toThrow(ValidationException::class);
});

it('requires the custom domain entitlement', function () {
    $tenant = $this->createTenant([
        'plan_id' => $this->createPlan([
            'features' => ['custom_domain' => false],
        ])->getKey(),
    ]);

    expect(fn () => app(DomainService::class)->addCustomDomain($tenant, 'blocked.example.com'))
        ->toThrow(FeatureNotAvailableException::class);
});

// ==== Verification ====

it('verifies ownership without activating ssl', function () {
    $tenant = $this->createTenant(['plan_id' => $this->createPlan([
        'features' => ['custom_domain' => true],
    ])->getKey()]);

    $this->app->instance(DomainOwnershipVerifier::class, new class implements DomainOwnershipVerifier
    {
        /**
         * Accept every challenge for the test.
         */
        public function verify(DomainChallengeContract $challenge, string $hostname): bool
        {
            return true;
        }
    });

    $service = app(DomainService::class);
    $domain = $service->addCustomDomain($tenant, 'verified.example.com');

    $result = $service->verify($domain);

    $domain->refresh();

    expect($result['verified'])->toBeTrue()
        ->and($domain->status)->toBe(DomainStatus::Verified)
        ->and($domain->verified_at)->not->toBeNull()
        ->and($domain->ssl_status)->toBe(SslStatus::Pending)
        ->and($domain->ssl_issued_at)->toBeNull();
});

it('records a failed verification attempt without verifying the domain', function () {
    $tenant = $this->createTenant(['plan_id' => $this->createPlan([
        'features' => ['custom_domain' => true],
    ])->getKey()]);

    $this->app->instance(DomainOwnershipVerifier::class, new class implements DomainOwnershipVerifier
    {
        /**
         * Reject every challenge for the test.
         */
        public function verify(DomainChallengeContract $challenge, string $hostname): bool
        {
            return false;
        }
    });

    $service = app(DomainService::class);
    $domain = $service->addCustomDomain($tenant, 'failed.example.com');

    $result = $service->verify($domain);

    $domain->refresh();

    expect($result['verified'])->toBeFalse()
        ->and($domain->status)->toBe(DomainStatus::Pending)
        ->and($domain->last_checked_at)->not->toBeNull();
});

it('refuses to make an unverified domain primary', function () {
    $tenant = $this->createTenant();
    $domain = app(DomainService::class)->addCustomDomain($tenant, 'pending.example.com');

    expect(fn () => app(DomainService::class)->setPrimary($tenant, $domain))
        ->toThrow(ValidationException::class);
});

it('refuses to change a domain owned by another tenant', function () {
    $owner = $this->createTenant();
    $other = $this->createTenant();
    $service = app(DomainService::class);

    $domain = $service->registerPlatformDomain($owner);

    expect(fn () => $service->setPrimary($other, $domain))
        ->toThrow(CrossTenantAccessException::class);
});

it('issues ssl only for a verified domain', function () {
    $tenant = $this->createTenant();
    $service = app(DomainService::class);

    $domain = $service->registerPlatformDomain($tenant);

    $service->markSslIssued($domain);

    expect($domain->refresh()->ssl_status)->toBe(SslStatus::Issued)
        ->and($domain->ssl_issued_at)->not->toBeNull();
});
