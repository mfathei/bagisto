<?php

namespace Webkul\Saas\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Webkul\Core\Repositories\ChannelRepository;
use Webkul\Saas\Console\Concerns\ResolvesTenant;
use Webkul\Saas\Enums\AuditAction;
use Webkul\Saas\Enums\TenantStatus;
use Webkul\Saas\Repositories\TenantRepository;
use Webkul\Saas\Services\AuditLogger;

class BootstrapTenantCommand extends Command
{
    use ResolvesTenant;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'saas:bootstrap-tenant
                            {tenant : Tenant ID, UUID or slug that receives the legacy data}
                            {--channel= : Existing channel code to attach to the tenant}
                            {--force : Skip the confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign legacy commerce rows with no tenant to an existing tenant; rows owned by another tenant are never touched.';

    /**
     * Execute the console command.
     */
    public function handle(
        TenantRepository $tenants,
        ChannelRepository $channels,
        AuditLogger $audit
    ): int {
        $tenant = $this->resolveTenantArgument($tenants, (string) $this->argument('tenant'));

        if (! $tenant) {
            $this->components->error('No tenant matches "'.$this->argument('tenant').'".');

            return self::FAILURE;
        }

        if ($tenant->status === TenantStatus::Cancelled) {
            $this->components->error('Tenant '.$tenant->slug.' is cancelled and cannot receive legacy data.');

            return self::FAILURE;
        }

        $channelCode = trim((string) $this->option('channel'));
        $channel = null;

        if ($channelCode !== '') {
            $channel = $channels->findOneByField('code', $channelCode);

            if (! $channel) {
                $this->components->error('No channel matches "'.$channelCode.'".');

                return self::FAILURE;
            }
        }

        $counts = $this->legacyCounts();

        if (array_sum($counts) === 0 && ! $channel) {
            $this->components->info('No unassigned legacy rows were found; nothing to do.');

            return self::SUCCESS;
        }

        $this->table(
            ['Model', 'Unassigned rows'],
            collect($counts)
                ->filter(fn (int $count) => $count > 0)
                ->map(fn (int $count, string $model) => [$model, $count])
                ->values()
                ->all()
        );

        if (! $this->option('force') && ! $this->confirm('Assign these rows to tenant '.$tenant->slug.'?', false)) {
            $this->components->info('Bootstrap cancelled; no rows were changed.');

            return self::SUCCESS;
        }

        $assigned = 0;

        DB::transaction(function () use ($counts, $tenant, $channel, &$assigned) {
            foreach ($counts as $model => $count) {
                if ($count === 0) {
                    continue;
                }

                $class = $this->modelClass($model);

                $assigned += $class::withoutGlobalScopes()
                    ->whereNull('tenant_id')
                    ->update(['tenant_id' => $tenant->getKey()]);
            }

            if ($channel) {
                $tenant->forceFill(['channel_id' => $channel->getKey()])->save();
            }
        });

        $audit->forTenant($tenant, AuditAction::LegacyDataAssigned, [
            'description' => 'Legacy commerce data assigned to the tenant.',
            'metadata' => [
                'rows' => $assigned,
                'models' => array_filter($counts),
                'channel' => $channel?->code,
            ],
        ]);

        $this->components->success($assigned.' legacy row(s) assigned to '.$tenant->slug.'.');

        return self::SUCCESS;
    }

    /**
     * Count unassigned rows for every tenant-owned model.
     *
     * @return array<string, int>
     */
    protected function legacyCounts(): array
    {
        $counts = [];

        foreach ((array) config('saas.tenant_owned_models', []) as $class) {
            if (! class_exists($class)) {
                continue;
            }

            $counts[class_basename($class)] = (int) $class::withoutGlobalScopes()
                ->whereNull('tenant_id')
                ->count();
        }

        return $counts;
    }

    /**
     * Resolve a model class from its basename.
     */
    protected function modelClass(string $basename): string
    {
        foreach ((array) config('saas.tenant_owned_models', []) as $class) {
            if (class_exists($class) && class_basename($class) === $basename) {
                return $class;
            }
        }

        throw new RuntimeException('Unknown tenant-owned model: '.$basename);
    }
}
