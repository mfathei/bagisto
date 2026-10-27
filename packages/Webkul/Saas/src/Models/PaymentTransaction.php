<?php

namespace Webkul\Saas\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Saas\Contracts\PaymentTransaction as PaymentTransactionContract;
use Webkul\Saas\Enums\PaymentStatus;
use Webkul\Saas\Traits\HasUuid;

class PaymentTransaction extends Model implements PaymentTransactionContract
{
    use HasFactory, HasUuid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'saas_payment_transactions';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'uuid',
        'billing_record_id',
        'provider',
        'provider_transaction_id',
        'amount',
        'currency',
        'status',
        'metadata',
        'processed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => PaymentStatus::class,
        'amount' => 'decimal:2',
        'metadata' => 'array',
        'processed_at' => 'datetime',
    ];

    /**
     * The invoice paid by the transaction.
     */
    public function billingRecord(): BelongsTo
    {
        return $this->belongsTo(BillingRecordProxy::modelClass());
    }
}
