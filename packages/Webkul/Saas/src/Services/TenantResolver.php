<?php

namespace Webkul\Saas\Services;

use Illuminate\Cache\CacheManager;
use Illuminate\Support\Facades\Log;
use Webkul\Saas\Contracts\Tenant;
use Webkul\Saas\Enums\DomainStatus;
use Webkul\Saas\Enums\SubscriptionStatus;
use Webkul\Saas\Enums\TenantStatus;
use Webkul\Saas\Exceptions\TenantResolutionException;
use Webkul\Saas\Repositories\TenantDomainRepository;
use Webkul\Saas\Repositories\TenantRepository;

class TenantResolver
{
    /**
     * Create a new resolver instance.
     */
    public function __construct(
        protected TenantDomainRepository $domainRepository,
        protected TenantRepository $tenantRepository,
        protected TenantContext $context,
        protected CacheManager $cache
    ) {}

    /**
     * Normalise and validate a request hostname.
     *
     * The raw Host header never becomes a query key before this runs.
     */
    public function normalizeHost(?string $host): ?string
    {
        if (! $host) {
            return null;
        }

        $host = trim($host);

        $host = preg_replace('#^[a-z][a-z0-9+.-]*://#i', '', $host) ?? $host;
        $host = explode('/', $host)[0];
        $host = explode('?', $host)[0];
        $host = explode('#', $host)[0];

        if (str_contains($host, '@')) {
            $host = substr($host, strrpos($host, '@') + 1);
        }

        if (! str_starts_with($host, '[')) {
            $host = preg_replace('/:\d+$/', '', $host) ?? $host;
        }

        $host = strtolower(rtrim($host, '.'));

        if ($host === '' || strlen($host) > 253) {
            return null;
        }

        if (function_exists('idn_to_ascii')) {
            $ascii = idn_to_ascii($host, IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);

            if ($ascii !== false) {
                $host = $ascii;
            }
        }

        $isIp = filter_var($host, FILTER_VALIDATE_IP) !== false;

        $isHostname = preg_match(
            '/^(?=.{1,253}$)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)*[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?$/',
            $host
        ) === 1;

        return $isIp || $isHostname ? $host : null;
    }

    /**
     * Whether the host belongs to the platform rather than a tenant.
     */
    public function isPlatformHost(?string $host): bool
    {
        $host = $this->normalizeHost($host);

        if (! $host) {
            return false;
        }

        $platform = $this->normalizeHost((string) config('saas.platform_domain'));

        if ($platform && ($host === $platform || $host === 'www.'.$platform)) {
            return true;
        }

        foreach ((array) config('saas.platform_hosts', []) as $configuredHost) {
            if ($this->normalizeHost($configuredHost) === $host) {
                return true;
            }
        }

        return false;
    }

    /**
     * Resolve the tenant for a hostname, or null when none is serving.
     */
    public function resolve(?string $host): ?Tenant
    {
        return $this->resolveWithReason($host)['tenant'];
    }

    /**
     * Resolve a hostname together with a failure reason.
     *
     * @return array{tenant: ?Tenant, reason: string}
     */
    public function resolveWithReason(?string $host): array
    {
        if (! config('saas.enabled')) {
            return ['tenant' => null, 'reason' => 'disabled'];
        }

        $host = $this->normalizeHost($host);

        if (! $host) {
            return ['tenant' => null, 'reason' => 'invalid_host'];
        }

        if ($this->isPlatformHost($host)) {
            return ['tenant' => null, 'reason' => 'platform'];
        }

        $cached = $this->cache()->get($this->cacheKey($host));

        if (is_array($cached)) {
            if (empty($cached['tenant_id'])) {
                return ['tenant' => null, 'reason' => $cached['reason'] ?? 'unknown'];
            }

            $tenant = $this->tenantRepository->find($cached['tenant_id']);

            if (! $tenant) {
                $this->forgetHost($host);

                return ['tenant' => null, 'reason' => 'unknown'];
            }

            $reason = $this->accessReason($tenant);

            if ($reason !== null) {
                return ['tenant' => null, 'reason' => $reason];
            }

            return ['tenant' => $tenant->loadMissing(['plan', 'channel', 'currentSubscription.plan']), 'reason' => 'ok'];
        }

        $domain = $this->domainRepository->findByHost($host);

        if (! $domain || ! $domain->tenant) {
            $this->remember($host, null, 'unknown');

            return ['tenant' => null, 'reason' => 'unknown'];
        }

        if ($domain->status !== DomainStatus::Verified) {
            $this->remember($host, null, 'domain_unverified');

            return ['tenant' => null, 'reason' => 'domain_unverified'];
        }

        $reason = $this->accessReason($domain->tenant);

        if ($reason !== null) {
            $this->remember($host, null, $reason);

            return ['tenant' => null, 'reason' => $reason];
        }

        $this->remember($host, $domain->tenant->getKey(), 'ok');

        return [
            'tenant' => $domain->tenant->loadMissing(['plan', 'channel', 'currentSubscription.plan']),
            'reason' => 'ok',
        ];
    }

    /**
     * Resolve a host or throw a non-disclosing failure.
     */
    public function resolveOrFail(?string $host): Tenant
    {
        $result = $this->resolveWithReason($host);

        if ($result['tenant']) {
            return $result['tenant'];
        }

        throw new TenantResolutionException(
            (string) $this->normalizeHost($host),
            $result['reason'],
            $this->messageFor($result['reason'])
        );
    }

    /**
     * Activate the resolved tenant for the request.
     */
    public function activate(?Tenant $tenant, string $source = 'host'): void
    {
        $this->context->set($tenant, $source);

        if ($tenant?->channel) {
            core()->setCurrentChannel($tenant->channel);
        }
    }

    /**
     * Forget a cached hostname after a domain or tenant change.
     */
    public function forgetHost(?string $host): void
    {
        $host = $this->normalizeHost($host);

        if ($host) {
            $this->cache()->forget($this->cacheKey($host));
        }
    }

    /**
     * Forget every cached hostname belonging to a tenant.
     */
    public function forgetForTenant(Tenant $tenant): void
    {
        foreach ($tenant->domains()->pluck('hostname') as $hostname) {
            $this->forgetHost($hostname);
        }

        $this->forgetHost($this->platformStorefrontHost($tenant));
    }

    /**
     * The deterministic platform hostname for a tenant.
     */
    public function platformStorefrontHost(Tenant $tenant): string
    {
        $suffix = ltrim((string) config('saas.domains.suffix'), '.');

        return strtolower($tenant->slug.'.'.($suffix ?: config('saas.platform_domain')));
    }

    /**
     * The reason a tenant cannot serve traffic, if any.
     */
    protected function accessReason(Tenant $tenant): ?string
    {
        if ($tenant->status === TenantStatus::Suspended || $tenant->status === TenantStatus::Cancelled) {
            return 'suspended';
        }

        if ($tenant->status !== TenantStatus::Active) {
            return 'inactive';
        }

        $subscription = $tenant->currentSubscription;

        if ($subscription && ! in_array($subscription->status, [
            SubscriptionStatus::Trialing,
            SubscriptionStatus::Active,
            SubscriptionStatus::PastDue,
        ], true)) {
            return 'inactive';
        }

        return null;
    }

    /**
     * Cache a successful or negative lookup.
     */
    protected function remember(string $host, ?int $tenantId, string $reason): void
    {
        $ttl = $tenantId
            ? (int) config('saas.cache.ttl', 300)
            : (int) config('saas.domains.verification_cache_ttl', 60);

        $this->cache()->put($this->cacheKey($host), [
            'tenant_id' => $tenantId,
            'reason' => $reason,
        ], $ttl);
    }

    /**
     * The cache key for a hostname.
     */
    protected function cacheKey(string $host): string
    {
        return config('saas.cache.prefix', 'saas').':host:'.sha1($host);
    }

    /**
     * The cache store used for resolution.
     */
    protected function cache()
    {
        try {
            return $this->cache->store(config('saas.cache.store'));
        } catch (\Throwable $exception) {
            Log::warning('SaaS cache store unavailable, falling back to array.', [
                'exception' => $exception->getMessage(),
            ]);

            return $this->cache->store('array');
        }
    }

    /**
     * A tenant-safe failure message.
     */
    protected function messageFor(string $reason): string
    {
        return match ($reason) {
            'platform' => 'The platform host is not a tenant storefront.',
            'suspended' => trans('saas::app.errors.store-unavailable'),
            'inactive' => trans('saas::app.errors.store-unavailable'),
            default => trans('saas::app.errors.store-not-found'),
        };
    }
}
