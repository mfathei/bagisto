<?php

namespace Webkul\Saas\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Throwable;
use Webkul\Category\Contracts\Category;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Core\Contracts\Channel;
use Webkul\Core\Repositories\ChannelRepository;
use Webkul\Inventory\Contracts\InventorySource;
use Webkul\Inventory\Repositories\InventorySourceRepository;
use Webkul\Saas\Contracts\Plan;
use Webkul\Saas\Contracts\ProvisioningOperation;
use Webkul\Saas\Contracts\Subscription;
use Webkul\Saas\Contracts\Tenant;
use Webkul\Saas\Enums\AuditAction;
use Webkul\Saas\Enums\MembershipRole;
use Webkul\Saas\Enums\MembershipStatus;
use Webkul\Saas\Enums\ProvisioningStatus;
use Webkul\Saas\Enums\TenantStatus;
use Webkul\Saas\Exceptions\ProvisioningFailedException;
use Webkul\Saas\Mail\TenantApprovedMail;
use Webkul\Saas\Repositories\PlanRepository;
use Webkul\Saas\Repositories\PlatformAdminRepository;
use Webkul\Saas\Repositories\ProvisioningOperationRepository;
use Webkul\Saas\Repositories\TenantRepository;
use Webkul\Saas\Repositories\TenantUserRepository;
use Webkul\User\Contracts\Admin;
use Webkul\User\Contracts\Role;
use Webkul\User\Repositories\AdminRepository;

class ProvisioningService
{
    /**
     * Create a new provisioning service.
     */
    public function __construct(
        protected TenantRepository $tenants,
        protected ProvisioningOperationRepository $operations,
        protected TenantUserRepository $memberships,
        protected PlanRepository $plans,
        protected SubscriptionService $subscriptions,
        protected DomainService $domains,
        protected TenantRoleService $tenantRoles,
        protected AdminRepository $admins,
        protected PlatformAdminRepository $platformAdmins,
        protected InventorySourceRepository $inventorySources,
        protected CategoryRepository $categories,
        protected ChannelRepository $channels,
        protected PlatformSettings $settings,
        protected TenantContext $context,
        protected TenantResolver $resolver,
        protected AuditLogger $audit
    ) {}

    /**
     * Provision a tenant idempotently, recording every attempt.
     *
     * @param  array{owner?: array{name: string, email: string, password?: string}, trial_days?: int}  $context
     */
    public function provision(Tenant $tenant, array $context = []): ProvisioningOperation
    {
        if ($tenant->status === TenantStatus::Cancelled) {
            throw new ProvisioningFailedException('cancelled', trans('saas::app.errors.tenant-cancelled'));
        }

        if ($tenant->status === TenantStatus::Suspended) {
            throw new ProvisioningFailedException('suspended', trans('saas::app.errors.tenant-suspended'));
        }

        $latest = $this->operations->latestForTenant($tenant->getKey());

        if ($latest && $latest->status === ProvisioningStatus::Completed && $tenant->status === TenantStatus::Active) {
            return $latest;
        }

        $operation = $this->startOperation($tenant);

        $this->audit->forTenant($tenant, AuditAction::ProvisioningStarted, [
            'description' => 'Provisioning attempt '.$operation->attempts.'.',
            'metadata' => ['attempt' => $operation->attempts],
        ]);

        try {
            $result = $this->context->run($tenant, function () use ($tenant, $context, $operation) {
                return DB::transaction(fn () => $this->runSteps($tenant, $context, $operation));
            }, 'provisioning');
        } catch (Throwable $exception) {
            $this->recordFailure($tenant, $operation, $exception);

            throw $exception instanceof ProvisioningFailedException
                ? $exception
                : new ProvisioningFailedException((string) $operation->step, $exception->getMessage(), $exception);
        }

        $operation->forceFill([
            'status' => ProvisioningStatus::Completed->value,
            'step' => $result['step'],
            'error' => null,
            'finished_at' => now(),
        ])->save();

        $this->audit->forTenant($tenant, AuditAction::ProvisioningCompleted, [
            'description' => 'Provisioning completed.',
            'metadata' => ['step' => $result['step'], 'activated' => $result['activated']],
        ]);

        return $operation->refresh();
    }

    /**
     * Retry the latest failed provisioning attempt of a tenant.
     */
    public function retry(Tenant $tenant): ProvisioningOperation
    {
        $latest = $this->operations->latestForTenant($tenant->getKey());

        if ($latest && $latest->status === ProvisioningStatus::Completed && $tenant->status === TenantStatus::Active) {
            return $latest;
        }

        $this->audit->forTenant($tenant, AuditAction::ProvisioningRetried, [
            'description' => 'Provisioning retried by an administrator.',
        ]);

        return $this->provision($tenant);
    }

    /**
     * Run every provisioning step inside one transaction.
     *
     * @param  array<string, mixed>  $context
     * @return array{step: string, activated: bool}
     */
    protected function runSteps(Tenant $tenant, array $context, ProvisioningOperation $operation): array
    {
        $step = 'plan';
        $this->checkpoint($operation, $step);

        $plan = $this->resolvePlan($tenant);

        $step = 'role';
        $this->checkpoint($operation, $step);
        $role = $this->ensureRole($tenant);

        $step = 'owner';
        $this->checkpoint($operation, $step);
        $owner = $this->ensureOwner($tenant, $role, $context['owner'] ?? null);

        $step = 'membership';
        $this->checkpoint($operation, $step);
        $this->ensureMembership($tenant, $owner);

        $step = 'inventory';
        $this->checkpoint($operation, $step);
        $inventory = $this->ensureInventorySource($tenant);

        $step = 'root_category';
        $this->checkpoint($operation, $step);
        $category = $this->ensureRootCategory($tenant);

        $step = 'domain';
        $this->checkpoint($operation, $step);
        $domain = $this->domains->registerPlatformDomain($tenant);

        $step = 'channel';
        $this->checkpoint($operation, $step);
        $channel = $this->ensureChannel($tenant, $category, $inventory, $domain->hostname);

        $tenant->forceFill([
            'channel_id' => $channel->getKey(),
            'owner_admin_id' => $owner->getKey(),
            'plan_id' => $tenant->plan_id ?: $plan->getKey(),
            'settings' => array_merge($this->defaultSettings($tenant), (array) $tenant->settings),
        ])->save();

        $step = 'finalize';
        $this->checkpoint($operation, $step);

        $activated = $this->finalize($tenant, $plan, $context);

        return ['step' => $activated ? 'active' : 'awaiting_requirements', 'activated' => $activated];
    }

    /**
     * Resolve the plan attached to the tenant.
     */
    protected function resolvePlan(Tenant $tenant): Plan
    {
        $plan = $tenant->plan;

        if ($plan) {
            return $plan;
        }

        $plan = $this->plans->findBySlug($this->settings->defaultPlanSlug())
            ?: $this->plans->defaultPlan();

        if (! $plan) {
            throw new ProvisioningFailedException('plan', trans('saas::app.errors.no-plan-configured'));
        }

        $tenant->forceFill(['plan_id' => $plan->getKey()])->save();

        return $plan;
    }

    /**
     * Create the restricted Bagisto role used by tenant staff.
     */
    protected function ensureRole(Tenant $tenant): Role
    {
        return $this->tenantRoles->ensure($tenant);
    }

    /**
     * Ensure the owner admin account exists and belongs to the tenant.
     *
     * @param  array{name?: string, email?: string, password?: string}|null  $owner
     */
    protected function ensureOwner(Tenant $tenant, Role $role, ?array $owner): Admin
    {
        if ($tenant->owner_admin_id) {
            $admin = $this->admins->find($tenant->owner_admin_id);

            if ($admin) {
                $admin->forceFill([
                    'tenant_id' => $tenant->getKey(),
                    'role_id' => $role->getKey(),
                ])->save();

                return $admin;
            }
        }

        if (! $owner || empty($owner['email']) || empty($owner['name'])) {
            throw new ProvisioningFailedException('owner', trans('saas::app.errors.owner-required'));
        }

        $email = strtolower($owner['email']);

        $taken = $this->platformAdmins->findByEmail($email)
            || $this->admins->getModel()->newQuery()->withoutGlobalScopes()->where('email', $email)->exists();

        if ($taken) {
            throw new ProvisioningFailedException('owner', trans('saas::app.errors.email-taken'));
        }

        $admin = $this->admins->create([
            'name' => $owner['name'],
            'email' => $email,
            'password' => Hash::make((string) ($owner['password'] ?? Str::random(40))),
            'role_id' => $role->getKey(),
            'status' => 1,
        ]);

        $admin->forceFill(['tenant_id' => $tenant->getKey()])->save();

        return $admin;
    }

    /**
     * Ensure the owner membership is active.
     */
    protected function ensureMembership(Tenant $tenant, Admin $owner): void
    {
        $membership = $this->memberships->findMembership($tenant->getKey(), $owner->getKey());

        if ($membership) {
            $membership->forceFill([
                'role' => MembershipRole::Owner->value,
                'status' => MembershipStatus::Active->value,
                'accepted_at' => $membership->accepted_at ?? now(),
                'email_verified_at' => $tenant->email_verified_at,
            ])->save();

            return;
        }

        $this->memberships->create([
            'tenant_id' => $tenant->getKey(),
            'admin_id' => $owner->getKey(),
            'role' => MembershipRole::Owner->value,
            'status' => MembershipStatus::Active->value,
            'accepted_at' => now(),
            'email_verified_at' => $tenant->email_verified_at,
        ]);
    }

    /**
     * Ensure the tenant has a dedicated inventory source.
     */
    protected function ensureInventorySource(Tenant $tenant): InventorySource
    {
        $code = 't'.$tenant->getKey().'-main';

        $source = $this->inventorySources->findOneByField('code', $code);

        if ($source) {
            return $source;
        }

        return $this->inventorySources->create([
            'tenant_id' => $tenant->getKey(),
            'code' => $code,
            'name' => Str::limit($tenant->name, 60).' warehouse',
            'description' => 'Default fulfillment source for '.$tenant->name.'.',
            'contact_name' => $tenant->name,
            'contact_email' => $tenant->email,
            'contact_number' => $tenant->phone ?: '0000000000',
            'country' => 'US',
            'state' => 'NA',
            'city' => 'NA',
            'street' => 'NA',
            'postcode' => '00000',
            'priority' => 0,
            'status' => 1,
        ]);
    }

    /**
     * Ensure the tenant has a root category for its catalog.
     */
    protected function ensureRootCategory(Tenant $tenant): Category
    {
        $slug = 't'.$tenant->getKey().'-root';

        $category = $this->categories->findBySlug($slug);

        if ($category) {
            return $category;
        }

        return $this->categories->create([
            'locale' => 'all',
            'tenant_id' => $tenant->getKey(),
            'name' => $tenant->name,
            'slug' => $slug,
            'description' => $tenant->name.' catalog',
            'parent_id' => null,
            'status' => 1,
            'position' => 0,
            'display_mode' => 'products_and_description',
        ]);
    }

    /**
     * Ensure the tenant storefront channel exists and points at its domain.
     */
    protected function ensureChannel(
        Tenant $tenant,
        Category $category,
        InventorySource $inventory,
        string $hostname
    ): Channel {
        $code = 'tenant-'.$tenant->getKey();

        $locales = core()->getAllLocales();
        $currencies = core()->getAllCurrencies();

        $defaultLocale = $locales->firstWhere('code', $tenant->default_locale) ?: $locales->first();
        $defaultCurrency = $currencies->firstWhere('code', $tenant->default_currency) ?: $currencies->first();

        if (! $defaultLocale || ! $defaultCurrency) {
            throw new ProvisioningFailedException('channel', trans('saas::app.errors.locale-currency-missing'));
        }

        $data = [
            'code' => $code,
            'name' => $tenant->name,
            'description' => $tenant->name.' online store',
            'theme' => 'default',
            'hostname' => $hostname,
            'root_category_id' => $category->getKey(),
            'default_locale_id' => $defaultLocale->getKey(),
            'base_currency_id' => $defaultCurrency->getKey(),
            'locales' => $locales->pluck('id')->all(),
            'currencies' => $currencies->pluck('id')->all(),
            'inventory_sources' => [$inventory->getKey()],
            'home_seo' => [
                'meta_title' => $tenant->name,
                'meta_description' => Str::limit((string) ($tenant->settings['tagline'] ?? $tenant->name), 160),
                'meta_keywords' => $tenant->name,
            ],
        ];

        $channel = $this->channels->findOneByField('code', $code);

        if ($channel) {
            return $this->channels->update($data, $channel->getKey());
        }

        return $this->channels->create($data);
    }

    /**
     * Activate a tenant once verification and approval requirements are met.
     *
     * @param  array<string, mixed>  $context
     */
    protected function finalize(Tenant $tenant, Plan $plan, array $context): bool
    {
        if (! $this->satisfiesActivation($tenant)) {
            if ($tenant->status === TenantStatus::Provisioning) {
                $tenant->forceFill(['status' => TenantStatus::Pending->value])->save();
            }

            return false;
        }

        $subscription = $this->ensureSubscription($tenant, $plan, $context);

        $wasActive = $tenant->status === TenantStatus::Active;

        $tenant->forceFill([
            'status' => TenantStatus::Active->value,
            'activated_at' => $tenant->activated_at ?? now(),
            'suspended_at' => null,
            'suspension_reason' => null,
            'plan_id' => $subscription->plan_id,
        ])->save();

        $this->resolver->forgetForTenant($tenant);

        if (! $wasActive) {
            $this->audit->forTenant($tenant, AuditAction::TenantActivated, [
                'description' => 'Tenant activated.',
            ]);

            $this->notifyActivated($tenant);
        }

        return true;
    }

    /**
     * Send the activation notice, logging rather than failing provisioning.
     */
    protected function notifyActivated(Tenant $tenant): void
    {
        if (! $tenant->email) {
            return;
        }

        try {
            Mail::to($tenant->email)->send(new TenantApprovedMail($tenant, $tenant->storefrontUrl()));
        } catch (Throwable $exception) {
            Log::warning('SaaS activation mail could not be sent.', [
                'tenant' => $tenant->uuid,
                'exception' => $exception->getMessage(),
            ]);
        }
    }

    /**
     * Ensure an access-granting subscription exists.
     *
     * @param  array<string, mixed>  $context
     */
    protected function ensureSubscription(Tenant $tenant, Plan $plan, array $context): Subscription
    {
        $current = $this->subscriptions->current($tenant);

        if ($current && $current->status->grantsAccess()) {
            return $current;
        }

        $trialDays = array_key_exists('trial_days', $context)
            ? (int) $context['trial_days']
            : $this->settings->defaultTrialDays();

        return $this->subscriptions->start($tenant->refresh(), $plan, $trialDays);
    }

    /**
     * Whether verification and approval allow the tenant to activate.
     */
    protected function satisfiesActivation(Tenant $tenant): bool
    {
        if ($this->settings->requiresEmailVerification() && ! $tenant->email_verified_at) {
            return false;
        }

        if ($this->settings->requiresApproval() && ! $tenant->approved_at) {
            return false;
        }

        return true;
    }

    /**
     * Record the current step on the operation.
     */
    protected function checkpoint(ProvisioningOperation $operation, string $step): void
    {
        $operation->forceFill([
            'step' => $step,
            'status' => ProvisioningStatus::Running->value,
            'started_at' => $operation->started_at ?? now(),
        ])->save();
    }

    /**
     * Create or reopen the tenant's provisioning operation.
     */
    protected function startOperation(Tenant $tenant): ProvisioningOperation
    {
        $key = 'tenant:'.$tenant->getKey().':provision';

        $operation = $this->operations->findByIdempotencyKey($key);

        if (! $operation) {
            $operation = $this->operations->create([
                'tenant_id' => $tenant->getKey(),
                'type' => 'provision',
                'status' => ProvisioningStatus::Running->value,
                'step' => 'starting',
                'attempts' => 1,
                'idempotency_key' => $key,
                'payload' => [
                    'owner_admin_id' => $tenant->owner_admin_id,
                    'plan_id' => $tenant->plan_id,
                ],
                'started_at' => now(),
            ]);
        } else {
            $operation->forceFill([
                'status' => ProvisioningStatus::Running->value,
                'attempts' => ((int) $operation->attempts) + 1,
                'error' => null,
                'started_at' => now(),
                'finished_at' => null,
            ])->save();
        }

        if ($tenant->status === TenantStatus::Pending) {
            $tenant->forceFill(['status' => TenantStatus::Provisioning->value])->save();
        }

        return $operation->refresh();
    }

    /**
     * Record a failed attempt without leaking secrets.
     */
    protected function recordFailure(Tenant $tenant, ProvisioningOperation $operation, Throwable $exception): void
    {
        $step = $exception instanceof ProvisioningFailedException ? $exception->step : (string) $operation->step;

        $operation->refresh();

        $operation->forceFill([
            'status' => ProvisioningStatus::Failed->value,
            'step' => $step,
            'error' => Str::limit($exception::class.': '.$exception->getMessage(), 2000),
            'finished_at' => now(),
        ])->save();

        if ($tenant->status === TenantStatus::Provisioning) {
            $tenant->forceFill(['status' => TenantStatus::Pending->value])->save();
        }

        $this->audit->forTenant($tenant, AuditAction::ProvisioningFailed, [
            'description' => 'Provisioning failed at step '.$step.'.',
            'metadata' => ['step' => $step, 'attempt' => $operation->attempts],
        ]);

        Log::error('SaaS tenant provisioning failed.', [
            'tenant_id' => $tenant->getKey(),
            'step' => $step,
            'exception' => $exception,
        ]);
    }

    /**
     * Default tenant settings written during provisioning.
     *
     * @return array<string, mixed>
     */
    protected function defaultSettings(Tenant $tenant): array
    {
        return [
            'timezone' => $tenant->timezone,
            'locale' => $tenant->default_locale,
            'currency' => $tenant->default_currency,
            'onboarding' => [
                'profile_completed' => false,
                'branding_completed' => false,
                'domain_completed' => false,
                'product_created' => false,
                'payment_configured' => false,
                'shipping_configured' => false,
                'published' => false,
            ],
        ];
    }
}
