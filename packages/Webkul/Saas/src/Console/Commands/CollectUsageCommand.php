<?php

namespace Webkul\Saas\Console\Commands;

use Illuminate\Console\Command;
use Throwable;
use Webkul\Saas\Console\Concerns\ResolvesTenant;
use Webkul\Saas\Enums\TenantStatus;
use Webkul\Saas\Repositories\TenantRepository;
use Webkul\Saas\Services\UsageService;

class CollectUsageCommand extends Command
{
    use ResolvesTenant;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saas:collect-usage
                            {--tenant= : Restrict the recalculation to a tenant ID, UUID or slug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate and persist usage metrics for active tenants.';

    /**
     * Execute the console command.
     */
    public function handle(TenantRepository $tenants, UsageService $usage): int
    {
        $identifier = $this->option('tenant');

        if ($identifier !== null && $identifier !== '') {
            return $this->collectForTenant($tenants, $usage, (string) $identifier);
        }

        $collected = 0;
        $failures = 0;

        $tenants->getModel()
            ->newQuery()
            ->where('status', TenantStatus::Active->value)
            ->orderBy('id')
            ->chunkById(100, function ($chunk) use ($usage, &$collected, &$failures) {
                foreach ($chunk as $tenant) {
                    try {
                        $usage->collect($tenant);
                        $collected++;
                    } catch (Throwable $exception) {
                        $failures++;
                        $this->components->warn('Usage collection failed for tenant '.$tenant->slug.': '.$exception->getMessage());
                    }
                }
            });

        $this->components->success($collected.' tenant usage snapshot(s) collected.');

        return $failures > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * Recalculate the metrics of one tenant and print them.
     */
    protected function collectForTenant(TenantRepository $tenants, UsageService $usage, string $identifier): int
    {
        $tenant = $this->resolveTenantArgument($tenants, $identifier);

        if (! $tenant) {
            $this->components->error('No tenant matches "'.$identifier.'".');

            return self::FAILURE;
        }

        try {
            $metrics = $usage->collect($tenant);
        } catch (Throwable $exception) {
            $this->components->error('Usage collection failed: '.$exception->getMessage());

            return self::FAILURE;
        }

        $this->table(
            ['Metric', 'Value'],
            collect($metrics)->map(fn ($value, $metric) => [$metric, $value])->values()->all()
        );

        $this->components->success('Usage metrics collected for '.$tenant->slug.'.');

        return self::SUCCESS;
    }
}
