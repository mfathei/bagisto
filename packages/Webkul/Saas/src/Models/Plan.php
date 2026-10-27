<?php

namespace Webkul\Saas\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Webkul\Saas\Contracts\Plan as PlanContract;
use Webkul\Saas\Enums\FeatureKey;
use Webkul\Saas\Traits\HasUuid;

class Plan extends Model implements PlanContract
{
    use HasFactory, HasUuid, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'saas_plans';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'uuid',
        'name',
        'slug',
        'description',
        'price',
        'yearly_price',
        'currency',
        'billing_interval',
        'trial_days',
        'premium_themes',
        'offer_status',
        'offer_title',
        'offer_type',
        'offer_discount',
        'limits',
        'features',
        'is_active',
        'is_public',
        'sort_order',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'trial_days' => 'integer',
        'premium_themes' => 'integer',
        'offer_discount' => 'decimal:2',
        'limits' => 'array',
        'features' => 'array',
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Subscriptions purchased on this plan.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(SubscriptionProxy::modelClass());
    }

    /**
     * The numeric quota for a feature, or null when unlimited.
     */
    public function limit(FeatureKey|string $key): ?int
    {
        $key = $key instanceof FeatureKey ? $key->value : $key;

        if (! array_key_exists($key, (array) $this->limits)) {
            return null;
        }

        $value = $this->limits[$key];

        return $value === null || $value === '' ? null : (int) $value;
    }

    /**
     * Whether a boolean feature is included in the plan.
     */
    public function includes(FeatureKey|string $key): bool
    {
        $key = $key instanceof FeatureKey ? $key->value : $key;

        return (bool) (($this->features ?? [])[$key] ?? false);
    }

    /**
     * Whether the plan can be purchased by new tenants.
     */
    public function isAvailable(): bool
    {
        return $this->is_active;
    }

    /**
     * Normalised monthly price used for MRR reporting.
     */
    public function monthlyPrice(): float
    {
        $price = (float) $this->price;

        return match ($this->billing_interval) {
            'yearly' => round($price / 12, 2),
            'quarterly' => round($price / 3, 2),
            default => $price,
        };
    }

    /**
     * Yearly amount advertised for the plan, falling back to twelve months.
     */
    public function yearlyAmount(): float
    {
        $yearly = (float) $this->yearly_price;

        return $yearly > 0 ? $yearly : round((float) $this->price * 12, 2);
    }

    /**
     * Whether the plan currently advertises an offer.
     */
    public function hasOffer(): bool
    {
        return $this->offer_status === 'active';
    }
}
