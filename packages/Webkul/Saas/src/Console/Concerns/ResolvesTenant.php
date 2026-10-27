<?php

namespace Webkul\Saas\Console\Concerns;

use Webkul\Saas\Contracts\Tenant;
use Webkul\Saas\Repositories\TenantRepository;

trait ResolvesTenant
{
    /**
     * Resolve a tenant from its numeric ID, UUID or slug.
     */
    protected function resolveTenantArgument(TenantRepository $tenants, string $value): ?Tenant
    {
        $value = trim($value);

        if ($value === '') {
            return null;
        }

        if (ctype_digit($value)) {
            $tenant = $tenants->find((int) $value);

            if ($tenant) {
                return $tenant;
            }
        }

        return $tenants->findByUuid($value) ?: $tenants->findBySlug(strtolower($value));
    }
}
