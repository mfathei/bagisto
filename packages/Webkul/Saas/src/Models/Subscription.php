<?php

namespace Webkul\Saas\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Saas\Contracts\Subscription as SubscriptionContract;
use Webkul\Saas\Enums\SubscriptionStatus;
use Webkul\Saas\Traits\HasUuid;

class Subscription extends Model implements SubscriptionContract
{
    use HasFactory, HasUuid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'saas_subscriptions';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'uuid',
        'tenant_id',
        'plan_id',
        'status',
        'starts_at',
        'ends_at',
        'trial_ends_at',
        'current_period_start',
        'current_period_end',
        'cancelled_at',
        'cancel_at_period_end',
        'grace_ends_at',
        'provider',
        'provider_subscription_id',
        'provider_customer_id',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => SubscriptionStatus::class,
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'trial_ends_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'cancelled_at' => 'datetime',
        'cancel_at_period_end' => 'boolean',
        'grace_ends_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * The tenant owning the subscription.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantProxy::modelClass());
    }

    /**
     * The purchased plan.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(PlanProxy::modelClass());
    }

    /**
     * Billing records generated for the subscription.
     */
    public function billingRecords(): HasMany
    {
        return $this->hasMany(BillingRecordProxy::modelClass());
    }

    /**
     * Whether a trial period is currently running.
     */
    public function onTrial(): bool
    {
        return $this->status === SubscriptionStatus::Trialing
            && $this->trial_ends_at
            && $this->trial_ends_at->isFuture();
    }

    /**
     * Whether the subscription is within a grace period.
     */
    public function inGracePeriod(): bool
    {
        return $this->grace_ends_at && $this->grace_ends_at->isFuture();
    }
}
