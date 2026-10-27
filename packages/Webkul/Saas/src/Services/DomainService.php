<?php

namespace Webkul\Saas\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Webkul\Saas\Contracts\DomainChallenge;
use Webkul\Saas\Contracts\DomainOwnershipVerifier;
use Webkul\Saas\Contracts\Tenant;
use Webkul\Saas\Contracts\TenantDomain;
use Webkul\Saas\Enums\AuditAction;
use Webkul\Saas\Enums\ChallengeStatus;
use Webkul\Saas\Enums\ChallengeType;
use Webkul\Saas\Enums\DomainStatus;
use Webkul\Saas\Enums\DomainType;
use Webkul\Saas\Enums\FeatureKey;
use Webkul\Saas\Enums\SslStatus;
use Webkul\Saas\Exceptions\CrossTenantAccessException;
use Webkul\Saas\Repositories\DomainChallengeRepository;
use Webkul\Saas\Repositories\TenantDomainRepository;
use Webkul\Saas\Repositories\TenantRepository;

class DomainService
{
    /**
     * Number of failed checks before the current challenge is discarded.
     */
    protected const MAX_ATTEMPTS = 20;

    /**
     * Create a new domain service.
     */
    public function __construct(
        protected TenantDomainRepository $domains,
        protected DomainChallengeRepository $challenges,
        protected TenantRepository $tenants,
        protected DomainOwnershipVerifier $verifier,
        protected EntitlementService $entitlements,
        protected TenantResolver $resolver,
        protected AuditLogger $audit
    ) {}

    /**
     * The suffix used for platform subdomains.
     */
    public function suffix(): string
    {
        return ltrim((string) config('saas.domains.suffix'), '.');
    }

    /**
     * The deterministic platform hostname of a slug.
     */
    public function platformHostFor(string $slug): string
    {
        return strtolower($slug.'.'.$this->suffix());
    }

    /**
     * Validate a subdomain slug and reject reserved or duplicate values.
     */
    public function assertSlugAvailable(string $slug, ?int $exceptTenantId = null): string
    {
        $slug = strtolower(trim($slug));

        if (! preg_match((string) config('saas.tenants.slug_regex'), $slug)) {
            throw ValidationException::withMessages([
                'slug' => trans('saas::app.errors.invalid-slug'),
            ]);
        }

        if (in_array($slug, (array) config('saas.domains.reserved_subdomains', []), true)) {
            throw ValidationException::withMessages([
                'slug' => trans('saas::app.errors.reserved-subdomain'),
            ]);
        }

        $tenant = $this->tenants->findBySlug($slug);

        if ($tenant && $tenant->getKey() !== $exceptTenantId) {
            throw ValidationException::withMessages([
                'slug' => trans('saas::app.errors.slug-taken'),
            ]);
        }

        if ($this->domains->hostnameTaken($this->platformHostFor($slug))) {
            throw ValidationException::withMessages([
                'slug' => trans('saas::app.errors.slug-taken'),
            ]);
        }

        return $slug;
    }

    /**
     * Register the verified platform subdomain of a tenant.
     */
    public function registerPlatformDomain(Tenant $tenant, ?string $hostname = null): TenantDomain
    {
        $hostname = $this->normalizeOrFail($hostname ?: $this->platformHostFor($tenant->slug));

        $existing = $this->domains->where('tenant_id', $tenant->getKey())
            ->where('hostname', $hostname)
            ->first();

        $autoVerify = (bool) config('saas.domains.auto_verify_platform_subdomains');

        $attributes = [
            'type' => DomainType::PlatformSubdomain->value,
            'status' => $autoVerify ? DomainStatus::Verified->value : DomainStatus::Pending->value,
            'verified_at' => $autoVerify ? now() : null,
            'is_primary' => $existing?->is_primary ?? ! $this->hasPrimary($tenant),
            'failure_reason' => null,
        ];

        if ($existing) {
            $existing->fill($attributes)->save();

            $domain = $existing;
        } else {
            $domain = $this->domains->create(array_merge($attributes, [
                'tenant_id' => $tenant->getKey(),
                'hostname' => $hostname,
                'ssl_status' => SslStatus::Pending->value,
            ]));
        }

        $this->resolver->forgetHost($hostname);

        return $domain;
    }

    /**
     * Attach a customer-owned hostname pending DNS verification.
     */
    public function addCustomDomain(Tenant $tenant, string $hostname): TenantDomain
    {
        if (! config('saas.domains.allow_custom')) {
            throw ValidationException::withMessages([
                'hostname' => trans('saas::app.errors.custom-domains-disabled'),
            ]);
        }

        $this->entitlements->assertFeature($tenant, FeatureKey::CustomDomain);

        $hostname = $this->normalizeOrFail($hostname);

        if (filter_var($hostname, FILTER_VALIDATE_IP)) {
            throw ValidationException::withMessages([
                'hostname' => trans('saas::app.errors.invalid-domain'),
            ]);
        }

        if ($this->resolver->isPlatformHost($hostname)) {
            throw ValidationException::withMessages([
                'hostname' => trans('saas::app.errors.platform-domain'),
            ]);
        }

        if (str_ends_with($hostname, '.'.$this->suffix()) || $hostname === $this->suffix()) {
            throw ValidationException::withMessages([
                'hostname' => trans('saas::app.errors.use-subdomain'),
            ]);
        }

        if ($this->domains->hostnameTaken($hostname)) {
            throw ValidationException::withMessages([
                'hostname' => trans('saas::app.errors.domain-taken'),
            ]);
        }

        $domain = $this->domains->create([
            'tenant_id' => $tenant->getKey(),
            'hostname' => $hostname,
            'type' => DomainType::Custom->value,
            'status' => DomainStatus::Pending->value,
            'is_primary' => false,
            'ssl_status' => SslStatus::Pending->value,
        ]);

        $this->issueChallenge($domain);

        $this->audit->forTenant($tenant, AuditAction::DomainAdded, [
            'subject' => $domain,
            'description' => 'Custom domain added.',
            'metadata' => ['hostname' => $hostname],
        ]);

        return $domain->refresh();
    }

    /**
     * Issue a fresh verification challenge for a pending domain.
     */
    public function issueChallenge(TenantDomain $domain, ChallengeType $type = ChallengeType::DnsTxt): DomainChallenge
    {
        $token = 'saas-verification='.bin2hex(random_bytes(24));

        return $this->challenges->create([
            'tenant_domain_id' => $domain->getKey(),
            'type' => $type->value,
            'token' => $token,
            'expected_value' => $token,
            'status' => ChallengeStatus::Pending->value,
            'expires_at' => now()->addSeconds((int) config('saas.domains.challenge_ttl', 86400)),
        ]);
    }

    /**
     * Check the domain challenge and verify the domain on success.
     *
     * @return array{verified: bool, message: string}
     */
    public function verify(TenantDomain $domain): array
    {
        $domain->loadMissing('tenant');

        if ($domain->status === DomainStatus::Verified) {
            return ['verified' => true, 'message' => trans('saas::app.domains.already-verified')];
        }

        $challenge = $this->challenges->pendingForDomain($domain->getKey());

        if (! $challenge) {
            $challenge = $this->issueChallenge($domain);
        }

        if ($challenge->expires_at && $challenge->expires_at->isPast()) {
            $challenge->forceFill(['status' => ChallengeStatus::Failed->value])->save();

            return ['verified' => false, 'message' => trans('saas::app.domains.challenge-expired')];
        }

        $verified = $this->verifier->verify($challenge, $domain->hostname);

        $challenge->forceFill([
            'attempts' => ($challenge->attempts ?? 0) + 1,
            'last_checked_at' => now(),
            'status' => $verified ? ChallengeStatus::Verified->value : ChallengeStatus::Pending->value,
            'verified_at' => $verified ? now() : null,
        ])->save();

        $domain->forceFill([
            'last_checked_at' => now(),
            'failure_reason' => $verified ? null : trans('saas::app.domains.verification-failed'),
        ]);

        if (! $verified) {
            if ($challenge->attempts >= self::MAX_ATTEMPTS) {
                $challenge->forceFill(['status' => ChallengeStatus::Failed->value])->save();
            }

            $domain->save();

            return ['verified' => false, 'message' => trans('saas::app.domains.verification-failed')];
        }

        $domain->forceFill([
            'status' => DomainStatus::Verified->value,
            'verified_at' => now(),
            'verification_token' => null,
            'verification_token_expires_at' => null,
        ])->save();

        $this->resolver->forgetHost($domain->hostname);

        $this->audit->forTenant($domain->tenant, AuditAction::DomainVerified, [
            'subject' => $domain,
            'description' => 'Domain ownership verified.',
            'metadata' => ['hostname' => $domain->hostname],
        ]);

        return ['verified' => true, 'message' => trans('saas::app.domains.verified')];
    }

    /**
     * Mark a verified domain as the tenant's primary hostname.
     */
    public function setPrimary(Tenant $tenant, TenantDomain $domain): TenantDomain
    {
        if ((int) $domain->tenant_id !== (int) $tenant->getKey()) {
            throw new CrossTenantAccessException;
        }

        if ($domain->status !== DomainStatus::Verified) {
            throw ValidationException::withMessages([
                'domain' => trans('saas::app.errors.domain-unverified'),
            ]);
        }

        DB::transaction(function () use ($tenant, $domain) {
            $this->domains->where('tenant_id', $tenant->getKey())->update(['is_primary' => false]);

            $domain->forceFill(['is_primary' => true])->save();

            $this->syncChannelHostname($tenant);
        });

        $this->resolver->forgetForTenant($tenant);

        $this->audit->forTenant($tenant, AuditAction::DomainPrimaryChanged, [
            'subject' => $domain,
            'metadata' => ['hostname' => $domain->hostname],
        ]);

        return $domain->refresh();
    }

    /**
     * Detach a domain, reassigning the primary flag when required.
     */
    public function remove(Tenant $tenant, TenantDomain $domain): void
    {
        if ((int) $domain->tenant_id !== (int) $tenant->getKey()) {
            throw new CrossTenantAccessException;
        }

        $hostname = $domain->hostname;
        $wasPrimary = (bool) $domain->is_primary;

        $domain->delete();

        if ($wasPrimary) {
            $replacement = $this->domains
                ->where('tenant_id', $tenant->getKey())
                ->where('status', DomainStatus::Verified->value)
                ->orderByDesc('id')
                ->first();

            if ($replacement) {
                $replacement->forceFill(['is_primary' => true])->save();
            }

            $this->syncChannelHostname($tenant);
        }

        $this->resolver->forgetHost($hostname);

        $this->audit->forTenant($tenant, AuditAction::DomainRemoved, [
            'description' => 'Domain removed.',
            'metadata' => ['hostname' => $hostname],
        ]);
    }

    /**
     * Record that a certificate was issued for a verified domain.
     */
    public function markSslIssued(TenantDomain $domain): TenantDomain
    {
        if ($domain->status !== DomainStatus::Verified) {
            throw ValidationException::withMessages([
                'domain' => trans('saas::app.errors.domain-unverified'),
            ]);
        }

        $domain->forceFill([
            'ssl_status' => SslStatus::Issued->value,
            'ssl_issued_at' => now(),
        ])->save();

        $this->audit->forTenant($domain->tenant, AuditAction::SslIssued, [
            'subject' => $domain,
            'metadata' => ['hostname' => $domain->hostname],
        ]);

        return $domain;
    }

    /**
     * Disable every hostname of a cancelled tenant.
     */
    public function disableAll(Tenant $tenant): void
    {
        foreach ($tenant->domains as $domain) {
            $domain->forceFill(['status' => DomainStatus::Disabled->value])->save();
        }

        $this->resolver->forgetForTenant($tenant);
    }

    /**
     * Normalise a hostname or fail validation.
     */
    protected function normalizeOrFail(string $hostname): string
    {
        $normalized = $this->resolver->normalizeHost($hostname);

        if (! $normalized) {
            throw ValidationException::withMessages([
                'hostname' => trans('saas::app.errors.invalid-domain'),
            ]);
        }

        return $normalized;
    }

    /**
     * Whether the tenant already has a primary domain.
     */
    protected function hasPrimary(Tenant $tenant): bool
    {
        return $this->domains
            ->where('tenant_id', $tenant->getKey())
            ->where('is_primary', true)
            ->exists();
    }

    /**
     * Point the tenant channel at the current primary hostname.
     */
    protected function syncChannelHostname(Tenant $tenant): void
    {
        $channel = $tenant->channel()->first();

        if (! $channel) {
            return;
        }

        $primary = $this->domains
            ->where('tenant_id', $tenant->getKey())
            ->where('is_primary', true)
            ->where('status', DomainStatus::Verified->value)
            ->first();

        $hostname = $primary?->hostname ?: $this->platformHostFor($tenant->slug);

        $channel->forceFill(['hostname' => $hostname])->save();
    }
}
