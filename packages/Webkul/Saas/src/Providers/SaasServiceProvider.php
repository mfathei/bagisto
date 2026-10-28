<?php

namespace Webkul\Saas\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Webkul\Core\Http\Middleware\NoCacheMiddleware;
use Webkul\Product\Models\Product;
use Webkul\Saas\Console\Commands\BootstrapTenantCommand;
use Webkul\Saas\Console\Commands\CollectUsageCommand;
use Webkul\Saas\Console\Commands\CreateSuperAdminCommand;
use Webkul\Saas\Console\Commands\InstallCommand;
use Webkul\Saas\Console\Commands\ProvisionTenantCommand;
use Webkul\Saas\Console\Commands\SendTrialWarningsCommand;
use Webkul\Saas\Console\Commands\SyncSubscriptionsCommand;
use Webkul\Saas\Console\Commands\VerifyDomainsCommand;
use Webkul\Saas\Contracts\BillingProvider;
use Webkul\Saas\Contracts\DomainOwnershipVerifier;
use Webkul\Saas\Enums\FeatureKey;
use Webkul\Saas\Enums\MembershipRole;
use Webkul\Saas\Exceptions\CrossTenantAccessException;
use Webkul\Saas\Http\Middleware\AuthenticateSuperAdmin;
use Webkul\Saas\Http\Middleware\EnsureFeature;
use Webkul\Saas\Http\Middleware\EnsurePlatformHost;
use Webkul\Saas\Http\Middleware\EnsureSuperAdminAbility;
use Webkul\Saas\Http\Middleware\EnsureTenantAdminContext;
use Webkul\Saas\Http\Middleware\ResolveTenantForApi;
use Webkul\Saas\Http\Middleware\ResolveTenantFromHost;
use Webkul\Saas\Http\Middleware\SetMarketingLocale;
use Webkul\Saas\Http\Middleware\SetTenantContextForAdmin;
use Webkul\Saas\Listeners\ScopeDataGridToTenant;
use Webkul\Saas\Models\Tenant;
use Webkul\Saas\Models\TenantUser;
use Webkul\Saas\Policies\TenantPolicy;
use Webkul\Saas\Scopes\TenantScope;
use Webkul\Saas\Services\Billing\ManualBillingProvider;
use Webkul\Saas\Services\Billing\UnconfiguredBillingProvider;
use Webkul\Saas\Services\DnsOwnershipVerifier;
use Webkul\Saas\Services\EntitlementService;
use Webkul\Saas\Services\TenantContext;
use Webkul\Saas\Services\TenantResolver;
use Webkul\Sales\Models\Order;

class SaasServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerConfig();

        $this->registerMarketingRoutes();

        $this->app->singleton(TenantContext::class);
        $this->app->singleton(TenantResolver::class);

        $this->app->bind(DomainOwnershipVerifier::class, DnsOwnershipVerifier::class);

        $this->app->bind(BillingProvider::class, function (Application $app) {
            return match ((string) config('saas.billing.provider', 'manual')) {
                'manual' => $app->make(ManualBillingProvider::class),
                default => $app->make(UnconfiguredBillingProvider::class),
            };
        });

        if ($this->app->runningInConsole()) {
            $this->commands([
                BootstrapTenantCommand::class,
                CollectUsageCommand::class,
                CreateSuperAdminCommand::class,
                InstallCommand::class,
                ProvisionTenantCommand::class,
                SendTrialWarningsCommand::class,
                SyncSubscriptionsCommand::class,
                VerifyDomainsCommand::class,
            ]);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(Router $router): void
    {
        $this->loadViewsFrom(__DIR__.'/../Resources/views', 'saas');

        $this->loadTranslationsFrom(__DIR__.'/../Resources/lang', 'saas');

        Blade::anonymousComponentPath(__DIR__.'/../Resources/views/marketing/components', 'marketing');

        $this->registerMiddleware($router);

        $this->registerRateLimiters();

        $this->registerRoutes();

        $this->registerDataGridScope();

        $this->registerTenantGuards();

        $this->registerQuotaGuards();

        Gate::policy(Tenant::class, TenantPolicy::class);

        $this->registerSchedule();
    }

    /**
     * Register the platform maintenance schedule.
     */
    protected function registerSchedule(): void
    {
        $this->callAfterResolving(Schedule::class, function (Schedule $schedule) {
            $schedule->command('saas:sync-subscriptions')->hourly();

            $schedule->command('saas:collect-usage')->daily();

            $schedule->command('saas:verify-domains')->everySixHours();

            $schedule->command('saas:send-trial-warnings')->daily();
        });
    }

    /**
     * Register package and admin configuration.
     */
    protected function registerConfig(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__).'/Config/saas.php', 'saas');

        $this->mergeConfigFrom(dirname(__DIR__).'/Config/marketing.php', 'saas.marketing');

        $this->mergeConfigFrom(dirname(__DIR__).'/Config/acl.php', 'acl');

        $this->mergeConfigFrom(dirname(__DIR__).'/Config/menu.php', 'menu.admin');
    }

    /**
     * Register middleware aliases and inject tenant resolution into the stacks.
     */
    protected function registerMiddleware(Router $router): void
    {
        $router->aliasMiddleware('saas.platform', EnsurePlatformHost::class);
        $router->aliasMiddleware('saas.super-admin', AuthenticateSuperAdmin::class);
        $router->aliasMiddleware('saas.super-admin.ability', EnsureSuperAdminAbility::class);
        $router->aliasMiddleware('saas.feature', EnsureFeature::class);
        $router->aliasMiddleware('saas.api.tenant', ResolveTenantForApi::class);
        $router->aliasMiddleware('saas.tenant.admin', EnsureTenantAdminContext::class);
        $router->aliasMiddleware('saas.marketing.locale', SetMarketingLocale::class);

        $router->prependMiddlewareToGroup('shop', ResolveTenantFromHost::class);

        $router->pushMiddlewareToGroup('web', SetTenantContextForAdmin::class);
    }

    /**
     * Register the public marketing routes for every platform host.
     *
     * These routes are registered during boot registration so they enter the
     * route collection before the storefront routes: the storefront owns a
     * catch-all on `/`, which would otherwise swallow the platform homepage.
     * Each host gets its own domain constraint, so tenant hosts still fall
     * through to their storefront.
     */
    protected function registerMarketingRoutes(): void
    {
        if (! config('saas.enabled')) {
            return;
        }

        foreach ($this->marketingHosts() as $host) {
            Route::domain($host)
                ->middleware(['web', 'saas.platform', 'saas.marketing.locale'])
                ->name('saas.marketing.')
                ->group(__DIR__.'/../Routes/marketing.php');
        }
    }

    /**
     * Normalised hosts that serve the platform website.
     *
     * @return string[]
     */
    protected function marketingHosts(): array
    {
        $hosts = [];

        $candidates = array_merge(
            [(string) config('saas.platform_domain')],
            (array) config('saas.platform_hosts', [])
        );

        foreach ($candidates as $candidate) {
            $host = strtolower(trim((string) $candidate));

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

            $host = rtrim($host, '.');

            if ($host === '') {
                continue;
            }

            $hosts[$host] = true;

            if (! filter_var($host, FILTER_VALIDATE_IP) && ! str_starts_with($host, 'www.')) {
                $hosts['www.'.$host] = true;
            }
        }

        return array_keys($hosts);
    }

    /**
     * Register the platform, tenant and API routes.
     */
    protected function registerRoutes(): void
    {
        Route::middleware(['web', 'saas.platform'])
            ->prefix(trim((string) config('saas.super_admin_prefix'), '/'))
            ->name('saas.super-admin.')
            ->group(__DIR__.'/../Routes/super-admin.php');

        Route::middleware(['web', 'saas.platform'])
            ->prefix('saas')
            ->name('saas.registration.')
            ->group(__DIR__.'/../Routes/platform.php');

        Route::middleware(['web', 'admin', NoCacheMiddleware::class, 'saas.tenant.admin'])
            ->prefix(trim((string) config('app.admin_url'), '/').'/saas')
            ->name('saas.tenant.')
            ->group(__DIR__.'/../Routes/tenant.php');

        Route::middleware(['web', 'shop'])
            ->prefix('saas/invitation')
            ->name('saas.invitation.')
            ->group(__DIR__.'/../Routes/invitation.php');

        Route::middleware(['api', 'auth:sanctum', 'saas.api.tenant', 'throttle:saas-api'])
            ->prefix('api/v1/saas')
            ->name('saas.api.')
            ->group(__DIR__.'/../Routes/api.php');
    }

    /**
     * Register the public rate limiters.
     */
    protected function registerRateLimiters(): void
    {
        RateLimiter::for('saas-registration', function (Request $request) {
            return Limit::perHour((int) config('saas.api.registration_rate_limit', 5))
                ->by('registration|'.$request->ip());
        });

        RateLimiter::for('saas-api', function (Request $request) {
            $tenantId = Auth::guard('sanctum')->user()?->tenant_id;

            return Limit::perMinute((int) config('saas.api.rate_limit', 60))
                ->by($tenantId ? 'tenant|'.$tenantId : 'ip|'.$request->ip());
        });

        RateLimiter::for('saas-contact', function (Request $request) {
            return Limit::perHour((int) config('saas.marketing.contact_rate_limit', 5))
                ->by('contact|'.$request->ip());
        });
    }

    /**
     * Scope raw datagrid queries to the active tenant.
     *
     * Bagisto datagrids query tables directly, bypassing Eloquent global
     * scopes, so tenant-owned rows would otherwise be listed to every tenant.
     */
    protected function registerDataGridScope(): void
    {
        Event::listen(
            'datagrid.*.query_builder.prepare.after',
            ScopeDataGridToTenant::class
        );
    }

    /**
     * Scope tenant-owned models and refuse cross-tenant writes.
     */
    protected function registerTenantGuards(): void
    {
        if (! config('saas.enabled')) {
            return;
        }

        $scope = $this->app->make(TenantScope::class);

        foreach ((array) config('saas.tenant_owned_models', []) as $modelClass) {
            if (! class_exists($modelClass) || ! is_subclass_of($modelClass, Model::class)) {
                continue;
            }

            if (! $modelClass::hasGlobalScope(TenantScope::class)) {
                $modelClass::addGlobalScope($scope);
            }

            $modelClass::creating(function (Model $model): void {
                $this->assignTenant($model);
            });

            $modelClass::updating(function (Model $model): void {
                $this->assertTenant($model);
            });

            $modelClass::deleting(function (Model $model): void {
                $this->assertTenant($model);
            });
        }
    }

    /**
     * Apply the active tenant when a record is created under a tenant context.
     */
    protected function assignTenant(Model $model): void
    {
        $context = $this->app->make(TenantContext::class);

        if (! $context->check()) {
            return;
        }

        $tenantId = $model->getAttribute('tenant_id');

        if ($tenantId === null) {
            $model->setAttribute('tenant_id', $context->id());

            return;
        }

        if ((int) $tenantId !== (int) $context->id()) {
            throw new CrossTenantAccessException;
        }
    }

    /**
     * Refuse changes to a record owned by another tenant.
     */
    protected function assertTenant(Model $model): void
    {
        $context = $this->app->make(TenantContext::class);

        if (! $context->check()) {
            return;
        }

        if ((int) $model->getAttribute('tenant_id') !== (int) $context->id()) {
            throw new CrossTenantAccessException;
        }
    }

    /**
     * Enforce quotas when the guarded resources are created.
     */
    protected function registerQuotaGuards(): void
    {
        if (! config('saas.enabled')) {
            return;
        }

        Product::creating(function (): void {
            $this->assertQuota(FeatureKey::MaxProducts);
        });

        Order::creating(function (): void {
            $this->assertQuota(FeatureKey::MonthlyOrders);
        });

        TenantUser::creating(function (TenantUser $membership): void {
            if ($membership->role === MembershipRole::Owner) {
                return;
            }

            $this->assertQuota(FeatureKey::MaxStaff);
        });
    }

    /**
     * Fail when the active tenant is at its plan quota.
     */
    protected function assertQuota(FeatureKey $feature): void
    {
        $context = $this->app->make(TenantContext::class);

        if (! $context->check()) {
            return;
        }

        $tenant = $context->tenant();

        $this->app->make(EntitlementService::class)->assertCanCreate($tenant, $feature);
    }
}
