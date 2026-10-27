<?php

namespace Webkul\Saas\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Saas\Contracts\BillingRecord as BillingRecordContract;
use Webkul\Saas\Enums\BillingStatus;
use Webkul\Saas\Traits\HasUuid;

class BillingRecord extends Model implements BillingRecordContract
{
    use HasFactory, HasUuid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'saas_billing_records';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'uuid',
        'tenant_id',
        'subscription_id',
        'number',
        'status',
        'currency',
        'subtotal',
        'tax',
        'total',
        'amount_paid',
        'period_start',
        'period_end',
        'due_at',
        'paid_at',
        'provider',
        'provider_invoice_id',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => BillingStatus::class,
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'total' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'due_at' => 'datetime',
        'paid_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * The tenant billed.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantProxy::modelClass());
    }

    /**
     * The subscription billed.
     */
    public function subscription(): BelongsTo
    {
        return $this->belongsTo(SubscriptionProxy::modelClass());
    }

    /**
     * Payments recorded against the invoice.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransactionProxy::modelClass());
    }

    /**
     * The outstanding balance.
     */
    public function balance(): float
    {
        return max(0, (float) $this->total - (float) $this->amount_paid);
    }
}
