<?php

namespace Webkul\Saas\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;
use Webkul\Saas\Repositories\TenantRepository;
use Webkul\Saas\Services\ProvisioningService;
use Webkul\Saas\Services\TenantContext;

class ProvisionTenant implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Number of attempts before the job is considered failed.
     */
    public int $tries = 3;

    /**
     * Delay in seconds between attempts.
     *
     * @var int[]
     */
    public array $backoff = [60, 300, 900];

    /**
     * Create a new job instance.
     */
    public function __construct(public int $tenantId) {}

    /**
     * Provision the tenant and always clear the context afterwards.
     */
    public function handle(ProvisioningService $provisioning, TenantRepository $tenants, TenantContext $context): void
    {
        $context->clear();

        $tenant = $tenants->find($this->tenantId);

        if (! $tenant) {
            return;
        }

        try {
            $provisioning->provision($tenant);
        } finally {
            $context->clear();
        }
    }

    /**
     * Clear the tenant context if the job fails on the final attempt.
     */
    public function failed(?Throwable $exception): void
    {
        app(TenantContext::class)->clear();
    }
}
