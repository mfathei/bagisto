<?php

namespace Webkul\Saas\Services;

use Closure;
use Webkul\Saas\Contracts\Tenant;
use Webkul\Saas\Exceptions\TenantContextMissingException;

class TenantContext
{
    /**
     * The tenant resolved for the current execution context.
     */
    protected ?Tenant $tenant = null;

    /**
     * How the current tenant was resolved.
     */
    protected ?string $source = null;

    /**
     * The resolved tenant, if any.
     */
    public function tenant(): ?Tenant
    {
        return $this->tenant;
    }

    /**
     * The current tenant identifier.
     */
    public function id(): ?int
    {
        return $this->tenant?->getKey();
    }

    /**
     * Whether a tenant context is active.
     */
    public function check(): bool
    {
        return $this->tenant !== null;
    }

    /**
     * The current tenant or a hard failure, the deny-by-default path.
     */
    public function requireTenant(): Tenant
    {
        if (! $this->tenant) {
            throw new TenantContextMissingException;
        }

        return $this->tenant;
    }

    /**
     * Activate a tenant for the current execution.
     */
    public function set(?Tenant $tenant, string $source = 'manual'): void
    {
        $this->tenant = $tenant;
        $this->source = $tenant ? $source : null;
    }

    /**
     * The source that resolved the current tenant.
     */
    public function source(): ?string
    {
        return $this->source;
    }

    /**
     * Run a callback under a tenant, restoring the previous context afterwards.
     */
    public function run(Tenant $tenant, Closure $callback, string $source = 'scoped'): mixed
    {
        $previousTenant = $this->tenant;
        $previousSource = $this->source;

        $this->set($tenant, $source);

        try {
            return $callback($tenant);
        } finally {
            $this->tenant = $previousTenant;
            $this->source = $previousSource;
        }
    }

    /**
     * Clear the context, used between requests and queue jobs.
     */
    public function clear(): void
    {
        $this->tenant = null;
        $this->source = null;
    }
}
