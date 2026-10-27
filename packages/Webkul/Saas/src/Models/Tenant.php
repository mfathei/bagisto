<?php

namespace Webkul\Saas\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;
use Webkul\Core\Models\ChannelProxy;
use Webkul\Saas\Contracts\Tenant as TenantContract;
use Webkul\Saas\Enums\TenantStatus;
use Webkul\Saas\Traits\HasUuid;
use Webkul\User\Models\AdminProxy;

class Tenant extends Model implements TenantContract
{
    use HasFactory, HasUuid, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'saas_tenants';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'legal_name',
        'email',
        'phone',
        'cname',
        'logo',
        'status',
        'plan_id',
        'current_subscription_id',
        'channel_id',
        'owner_admin_id',
        'email_verified_at',
        'approved_at',
        'trial_ends_at',
        'activated_at',
        'suspended_at',
        'cancelled_at',
        'suspension_reason',
        'timezone',
        'default_locale',
        'default_currency',
        'settings',
        'meta',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => TenantStatus::class,
        'email_verified_at' => 'datetime',
        'approved_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'activated_at' => 'datetime',
        'suspended_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'settings' => 'array',
        'meta' => 'array',
    ];

    /**
     * The plan currently attached to the tenant.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanProxy::modelClass());
    }

    /**
     * The subscription currently attached to the tenant.
     */
    public function currentSubscription(): BelongsTo
    {
        return $this->belongsTo(SubscriptionProxy::modelClass(), 'current_subscription_id');
    }

    /**
     * All subscriptions, including historical records.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(SubscriptionProxy::modelClass());
    }

    /**
     * Domains owned by the tenant.
     */
    public function domains(): HasMany
    {
        return $this->hasMany(TenantDomainProxy::modelClass());
    }

    /**
     * The primary domain, when one is defined.
     */
    public function primaryDomain(): HasOne
    {
        return $this->hasOne(TenantDomainProxy::modelClass())->where('is_primary', true);
    }

    /**
     * Memberships linking Bagisto admins to the tenant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(TenantUserProxy::modelClass());
    }

    /**
     * Pending and historical invitations.
     */
    public function invitations(): HasMany
    {
        return $this->hasMany(TenantInvitationProxy::modelClass());
    }

    /**
     * Provisioning attempts.
     */
    public function provisioningOperations(): HasMany
    {
        return $this->hasMany(ProvisioningOperationProxy::modelClass());
    }

    /**
     * Usage snapshots.
     */
    public function usageMetrics(): HasMany
    {
        return $this->hasMany(UsageMetricProxy::modelClass());
    }

    /**
     * Billing records belonging to the tenant.
     */
    public function billingRecords(): HasMany
    {
        return $this->hasMany(BillingRecordProxy::modelClass());
    }

    /**
     * Feature overrides granted outside the plan.
     */
    public function entitlements(): HasMany
    {
        return $this->hasMany(TenantEntitlementProxy::modelClass());
    }

    /**
     * The Bagisto channel backing the storefront.
     */
    public function channel(): BelongsTo
    {
        return $this->belongsTo(ChannelProxy::modelClass());
    }

    /**
     * The owner admin account.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(AdminProxy::modelClass(), 'owner_admin_id');
    }

    /**
     * Whether the tenant storefront may serve requests.
     */
    public function isServing(): bool
    {
        return $this->status === TenantStatus::Active;
    }

    /**
     * Whether the tenant is waiting for approval or provisioning.
     */
    public function isPending(): bool
    {
        return in_array($this->status, [TenantStatus::Pending, TenantStatus::Provisioning], true);
    }

    /**
     * Whether the tenant is suspended or cancelled.
     */
    public function isBlocked(): bool
    {
        return in_array($this->status, [TenantStatus::Suspended, TenantStatus::Cancelled], true);
    }

    /**
     * The public storefront URL, preferring a verified custom domain.
     */
    public function storefrontUrl(): ?string
    {
        $domain = $this->relationLoaded('domains')
            ? ($this->domains->firstWhere('is_primary', true) ?: $this->domains->firstWhere('status', 'verified'))
            : ($this->domains()->where('is_primary', true)->first()
                ?: $this->domains()->where('status', 'verified')->first());

        if (! $domain) {
            return null;
        }

        $scheme = request()->isSecure() ? 'https' : 'http';

        return $scheme.'://'.$domain->hostname;
    }

    /**
     * Public URL of the tenant logo, when one has been uploaded.
     */
    public function logoUrl(): ?string
    {
        return $this->logo ? Storage::url($this->logo) : null;
    }

    /**
     * Administration panel sign-in URL for the tenant storefront.
     */
    public function adminPanelUrl(): ?string
    {
        $storefront = $this->storefrontUrl();

        if (! $storefront) {
            return null;
        }

        return rtrim($storefront, '/').'/'.trim((string) config('app.admin_url'), '/').'/login';
    }

    /**
     * Scope tenants with a given status.
     */
    public function scopeStatus($query, TenantStatus|string $status)
    {
        return $query->where('status', $status instanceof TenantStatus ? $status->value : $status);
    }

    /**
     * Scope tenants that may serve a storefront.
     */
    public function scopeServing($query)
    {
        return $query->where('status', TenantStatus::Active->value);
    }
}
