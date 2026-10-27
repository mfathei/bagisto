<?php

namespace Webkul\Saas\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\ValidationException;
use Webkul\Saas\Contracts\Plan;
use Webkul\Saas\Contracts\Tenant;
use Webkul\Saas\Enums\AuditAction;
use Webkul\Saas\Enums\MembershipRole;
use Webkul\Saas\Enums\MembershipStatus;
use Webkul\Saas\Enums\SubscriptionStatus;
use Webkul\Saas\Enums\TenantStatus;
use Webkul\Saas\Jobs\ExportTenantData;
use Webkul\Saas\Jobs\ProvisionTenant;
use Webkul\Saas\Repositories\PlanRepository;
use Webkul\Saas\Repositories\PlatformAdminRepository;
use Webkul\Saas\Repositories\TenantRepository;
use Webkul\Saas\Repositories\TenantUserRepository;
use Webkul\User\Contracts\Admin;
use Webkul\User\Repositories\AdminRepository;

class TenantManager
{
    /**
     * Create a new tenant manager.
     */
    public function __construct(
        protected TenantRepository $tenants,
        protected PlanRepository $plans,
        protected AdminRepository $admins,
        protected PlatformAdminRepository $platformAdmins,
        protected TenantUserRepository $memberships,
        protected TenantRoleService $tenantRoles,
        protected DomainService $domains,
        protected SubscriptionService $subscriptions,
        protected TenantResolver $resolver,
        protected PlatformSettings $settings,
        protected AuditLogger $audit
    ) {}

    /**
     * Create a pending tenant with its owner account and membership.
     *
     * @param  array<string, mixed>  $data
     * @return array{tenant: Tenant, owner: Admin}
     */
    public function create(array $data, bool $publicRegistration = false): array
    {
        $slug = $this->domains->assertSlugAvailable((string) ($data['slug'] ?? ''));
        $plan = $this->resolvePlan($data, $publicRegistration);

        $email = strtolower(trim((string) $data['owner_email']));

        if (strlen((string) ($data['owner_password'] ?? '')) < (int) config('saas.registration.password_min_length', 12)) {
            throw ValidationException::withMessages([
                'owner_password' => trans('saas::app.errors.password-weak'),
            ]);
        }

        if ($this->platformAdmins->findByEmail($email) || $this->admins->findOneByField('email', $email)) {
            throw ValidationException::withMessages([
                'owner_email' => trans('saas::app.errors.email-taken'),
            ]);
        }

        [$tenant, $owner] = DB::transaction(function () use ($data, $slug, $plan, $email, $publicRegistration) {
            $tenant = $this->tenants->create([
                'name' => $data['name'],
                'slug' => $slug,
                'legal_name' => $data['legal_name'] ?? $data['name'],
                'email' => $data['email'] ?? $email,
                'phone' => $data['phone'] ?? null,
                'status' => TenantStatus::Pending->value,
                'plan_id' => $plan->getKey(),
                'timezone' => $data['timezone'] ?? config('saas.tenants.default_timezone'),
                'default_locale' => $data['locale'] ?? config('saas.tenants.default_locale'),
                'default_currency' => $data['currency'] ?? config('saas.tenants.default_currency'),
                'email_verified_at' => ! empty($data['email_verified']) ? now() : null,
                'approved_at' => ! empty($data['approved']) ? now() : null,
                'settings' => $this->initialSettings($data),
                'meta' => ['registration' => $publicRegistration, 'requested_plan' => $plan->slug],
            ]);

            $role = $this->tenantRoles->ensure($tenant);

            $owner = $this->admins->create([
                'name' => $data['owner_name'],
                'email' => $email,
                'password' => Hash::make((string) $data['owner_password']),
                'role_id' => $role->getKey(),
                'status' => 1,
            ]);

            $owner->forceFill(['tenant_id' => $tenant->getKey()])->save();

            $this->memberships->create([
                'tenant_id' => $tenant->getKey(),
                'admin_id' => $owner->getKey(),
                'role' => MembershipRole::Owner->value,
                'status' => MembershipStatus::Active->value,
                'accepted_at' => now(),
                'email_verified_at' => $tenant->email_verified_at,
            ]);

            $tenant->forceFill(['owner_admin_id' => $owner->getKey()])->save();

            return [$tenant, $owner];
        });

        $this->audit->forTenant($tenant, AuditAction::TenantCreated, [
            'description' => 'Tenant created.',
            'metadata' => ['slug' => $slug, 'plan' => $plan->slug, 'public_registration' => $publicRegistration],
        ]);

        return ['tenant' => $tenant->refresh(), 'owner' => $owner];
    }

    /**
     * Send the signed email verification link for a tenant owner.
     */
    public function verificationUrl(Tenant $tenant): string
    {
        return URL::temporarySignedRoute(
            'saas.registration.verify',
            now()->addSeconds((int) config('saas.registration.verification_token_ttl', 172800)),
            ['tenant' => $tenant->uuid]
        );
    }

    /**
     * Update the descriptive details of a tenant.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Tenant $tenant, array $data): Tenant
    {
        $fields = [
            'name',
            'legal_name',
            'email',
            'phone',
            'timezone',
            'default_locale',
            'default_currency',
        ];

        $changes = [];

        foreach ($fields as $field) {
            if (! array_key_exists($field, $data)) {
                continue;
            }

            $tenant->setAttribute($field, $data[$field]);
            $changes[] = $field;
        }

        if (isset($data['settings']) && is_array($data['settings'])) {
            $tenant->setAttribute('settings', array_merge((array) $tenant->settings, $data['settings']));
            $changes[] = 'settings';
        }

        $tenant->save();

        $this->resolver->forgetForTenant($tenant);

        $this->audit->forTenant($tenant, AuditAction::TenantUpdated, [
            'description' => 'Tenant details updated.',
            'metadata' => ['fields' => $changes],
        ]);

        return $tenant->refresh();
    }

    /**
     * Mark the owner email as verified and provision when possible.
     */
    public function markEmailVerified(Tenant $tenant): Tenant
    {
        if ($tenant->email_verified_at) {
            return $tenant;
        }

        $tenant->forceFill(['email_verified_at' => now()])->save();

        $this->memberships->where('tenant_id', $tenant->getKey())
            ->where('admin_id', $tenant->owner_admin_id)
            ->update(['email_verified_at' => now()]);

        $this->audit->forTenant($tenant, AuditAction::TenantUpdated, [
            'description' => 'Owner email verified.',
            'metadata' => ['email_verified' => true],
        ]);

        $this->provisionIfEligible($tenant);

        return $tenant->refresh();
    }

    /**
     * Approve a registration and continue provisioning.
     */
    public function approve(Tenant $tenant): Tenant
    {
        if ($tenant->approved_at) {
            return $tenant;
        }

        $tenant->forceFill(['approved_at' => now()])->save();

        $this->audit->forTenant($tenant, AuditAction::TenantApproved, [
            'description' => 'Tenant registration approved.',
        ]);

        $this->provisionIfEligible($tenant);

        return $tenant->refresh();
    }

    /**
     * Activate a fully verified and approved tenant.
     */
    public function activate(Tenant $tenant): Tenant
    {
        if ($tenant->status === TenantStatus::Active) {
            return $tenant;
        }

        if (in_array($tenant->status, [TenantStatus::Cancelled], true)) {
            throw ValidationException::withMessages([
                'status' => trans('saas::app.errors.tenant-cancelled'),
            ]);
        }

        if ($tenant->status === TenantStatus::Suspended) {
            return $this->reactivate($tenant);
        }

        ProvisionTenant::dispatch($tenant->getKey());

        return $tenant->refresh();
    }

    /**
     * Suspend a tenant and its subscription.
     */
    public function suspend(Tenant $tenant, string $reason): Tenant
    {
        if (! $tenant->status->canTransitionTo(TenantStatus::Suspended)) {
            throw ValidationException::withMessages([
                'status' => trans('saas::app.errors.invalid-status-transition'),
            ]);
        }

        $tenant->forceFill([
            'status' => TenantStatus::Suspended->value,
            'suspended_at' => now(),
            'suspension_reason' => $reason,
        ])->save();

        $subscription = $this->subscriptions->current($tenant);

        if ($subscription && $subscription->status->grantsAccess()) {
            $this->subscriptions->suspend($tenant, $reason);
        }

        $this->resolver->forgetForTenant($tenant);

        $this->audit->forTenant($tenant, AuditAction::TenantSuspended, [
            'description' => 'Tenant suspended.',
            'metadata' => ['reason' => $reason],
        ]);

        return $tenant->refresh();
    }

    /**
     * Lift a suspension and resume the subscription.
     */
    public function reactivate(Tenant $tenant): Tenant
    {
        if ($tenant->status !== TenantStatus::Suspended) {
            throw ValidationException::withMessages([
                'status' => trans('saas::app.errors.invalid-status-transition'),
            ]);
        }

        $tenant->forceFill([
            'status' => TenantStatus::Active->value,
            'suspended_at' => null,
            'suspension_reason' => null,
        ])->save();

        $subscription = $this->subscriptions->current($tenant);

        if ($subscription && $subscription->status === SubscriptionStatus::Suspended) {
            $this->subscriptions->resume($tenant);
        }

        $this->resolver->forgetForTenant($tenant);

        $this->audit->forTenant($tenant, AuditAction::TenantReactivated, [
            'description' => 'Tenant reactivated.',
        ]);

        return $tenant->refresh();
    }

    /**
     * Cancel a tenant, its subscription and its domains.
     */
    public function cancel(Tenant $tenant, string $reason): Tenant
    {
        if ($tenant->status === TenantStatus::Cancelled) {
            return $tenant;
        }

        $tenant->forceFill([
            'status' => TenantStatus::Cancelled->value,
            'cancelled_at' => now(),
            'suspension_reason' => $reason,
        ])->save();

        $subscription = $this->subscriptions->current($tenant);

        if ($subscription && $subscription->status->canTransitionTo(SubscriptionStatus::Cancelled)) {
            $this->subscriptions->cancel($tenant, false, $reason);
        }

        $this->domains->disableAll($tenant);

        $this->resolver->forgetForTenant($tenant);

        $this->audit->forTenant($tenant, AuditAction::TenantCancelled, [
            'description' => 'Tenant cancelled.',
            'metadata' => ['reason' => $reason],
        ]);

        return $tenant->refresh();
    }

    /**
     * Soft delete a cancelled tenant without destroying billing history.
     */
    public function delete(Tenant $tenant, string $confirmation): void
    {
        if (strtoupper(trim($confirmation)) !== strtoupper($tenant->slug)) {
            throw ValidationException::withMessages([
                'confirmation' => trans('saas::app.errors.delete-confirmation'),
            ]);
        }

        if ($tenant->status !== TenantStatus::Cancelled) {
            $this->cancel($tenant, 'Tenant deleted by a platform administrator.');
            $tenant->refresh();
        }

        $this->domains->disableAll($tenant);

        $this->resolver->forgetForTenant($tenant);

        $this->audit->forTenant($tenant, AuditAction::TenantDeleted, [
            'description' => 'Tenant soft deleted.',
            'metadata' => ['slug' => $tenant->slug],
        ]);

        $tenant->delete();
    }

    /**
     * Queue a tenant data export.
     */
    public function export(Tenant $tenant): void
    {
        $this->audit->forTenant($tenant, AuditAction::TenantExported, [
            'description' => 'Tenant data export requested.',
        ]);

        ExportTenantData::dispatch($tenant->getKey(), auth('super_admin')->id());
    }

    /**
     * Dispatch provisioning when the tenant meets the activation requirements.
     */
    protected function provisionIfEligible(Tenant $tenant): void
    {
        if ($this->settings->requiresEmailVerification() && ! $tenant->email_verified_at) {
            return;
        }

        if ($this->settings->requiresApproval() && ! $tenant->approved_at) {
            return;
        }

        if ($tenant->status === TenantStatus::Cancelled) {
            return;
        }

        ProvisionTenant::dispatch($tenant->getKey());
    }

    /**
     * Resolve the requested subscription plan.
     *
     * @param  array<string, mixed>  $data
     */
    protected function resolvePlan(array $data, bool $publicRegistration): Plan
    {
        $plan = null;

        if (! empty($data['plan_id'])) {
            $plan = $this->plans->find($data['plan_id']);
        } elseif (! empty($data['plan_slug'])) {
            $plan = $this->plans->findBySlug((string) $data['plan_slug']);
        } else {
            $plan = $this->plans->findBySlug($this->settings->defaultPlanSlug())
                ?: $this->plans->defaultPlan();
        }

        if (! $plan || ! $plan->is_active || ($publicRegistration && ! $plan->is_public)) {
            throw ValidationException::withMessages([
                'plan_id' => trans('saas::app.errors.plan-unavailable'),
            ]);
        }

        return $plan;
    }

    /**
     * Initial tenant settings recorded at registration.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function initialSettings(array $data): array
    {
        return [
            'tagline' => $data['tagline'] ?? null,
            'contact_email' => $data['email'] ?? $data['owner_email'],
            'contact_phone' => $data['phone'] ?? null,
            'timezone' => $data['timezone'] ?? config('saas.tenants.default_timezone'),
            'locale' => $data['locale'] ?? config('saas.tenants.default_locale'),
            'currency' => $data['currency'] ?? config('saas.tenants.default_currency'),
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
