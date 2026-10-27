<?php

namespace Webkul\Saas\Console\Commands;

use Illuminate\Console\Command;
use Throwable;
use Webkul\Saas\Console\Concerns\ResolvesTenant;
use Webkul\Saas\Contracts\Tenant;
use Webkul\Saas\Enums\TenantStatus;
use Webkul\Saas\Jobs\ProvisionTenant;
use Webkul\Saas\Repositories\TenantRepository;
use Webkul\Saas\Services\PlatformSettings;
use Webkul\Saas\Services\ProvisioningService;

class ProvisionTenantCommand extends Command
{
    use ResolvesTenant;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saas:provision-tenant
                            {tenant? : Tenant ID, UUID or slug}
                            {--all : Provision every eligible pending or provisioning tenant}
                            {--sync : Run provisioning in the foreground instead of queueing it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Queue or run tenant provisioning, including recovery of interrupted attempts.';

    /**
     * Execute the console command.
     */
    public function handle(TenantRepository $tenants, ProvisioningService $provisioning, PlatformSettings $settings): int
    {
        if ($this->option('all')) {
            return $this->provisionAll($tenants, $provisioning, $settings);
        }

        $identifier = trim((string) $this->argument('tenant'));

        if ($identifier === '') {
            $this->components->error('Provide a tenant ID, UUID or slug, or pass --all.');

            return self::FAILURE;
        }

        $tenant = $this->resolveTenantArgument($tenants, $identifier);

        if (! $tenant) {
            $this->components->error('No tenant matches "'.$identifier.'".');

            return self::FAILURE;
        }

        return $this->provisionOne($tenant, $provisioning);
    }

    /**
     * Provision one tenant, synchronously or through the queue.
     */
    protected function provisionOne(Tenant $tenant, ProvisioningService $provisioning): int
    {
        if (in_array($tenant->status, [TenantStatus::Cancelled, TenantStatus::Suspended], true)) {
            $this->components->error('Tenant '.$tenant->slug.' is '.$tenant->status->value.' and cannot be provisioned.');

            return self::FAILURE;
        }

        if ($tenant->status === TenantStatus::Active) {
            $this->components->info('Tenant '.$tenant->slug.' is already active.');

            return self::SUCCESS;
        }

        if (! $this->option('sync')) {
            ProvisionTenant::dispatch($tenant->getKey());

            $this->components->success('Provisioning queued for '.$tenant->slug.' (UUID '.$tenant->uuid.').');

            return self::SUCCESS;
        }

        try {
            $operation = $provisioning->provision($tenant);
        } catch (Throwable $exception) {
            $this->components->error('Provisioning failed for '.$tenant->slug.': '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Tenant', 'Status', 'Step', 'Attempts'],
            [[$tenant->slug, $operation->status->value, $operation->step, $operation->attempts]]
        );

        $this->components->success('Provisioning finished for '.$tenant->slug.'.');

        return self::SUCCESS;
    }

    /**
     * Provision every tenant that meets the activation requirements.
     */
    protected function provisionAll(TenantRepository $tenants, ProvisioningService $provisioning, PlatformSettings $settings): int
    {
        $candidates = $tenants->getModel()
            ->newQuery()
            ->whereIn('status', [TenantStatus::Pending->value, TenantStatus::Provisioning->value])
            ->orderBy('id')
            ->get()
            ->filter(function (Tenant $tenant) use ($settings): bool {
                if ($settings->requiresEmailVerification() && ! $tenant->email_verified_at) {
                    return false;
                }

                return ! ($settings->requiresApproval() && ! $tenant->approved_at);
            });

        if ($candidates->isEmpty()) {
            $this->components->info('No eligible tenants require provisioning.');

            return self::SUCCESS;
        }

        if (! $this->option('sync')) {
            $candidates->each(fn (Tenant $tenant) => ProvisionTenant::dispatch($tenant->getKey()));

            $this->components->success($candidates->count().' tenant(s) queued for provisioning.');

            return self::SUCCESS;
        }

        $failures = 0;

        foreach ($candidates as $tenant) {
            try {
                $provisioning->provision($tenant);
                $this->components->info('Provisioned '.$tenant->slug.'.');
            } catch (Throwable $exception) {
                $failures++;
                $this->components->warn('Provisioning failed for '.$tenant->slug.': '.$exception->getMessage());
            }
        }

        $this->components->success(($candidates->count() - $failures).' tenant(s) provisioned.');

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }
}
