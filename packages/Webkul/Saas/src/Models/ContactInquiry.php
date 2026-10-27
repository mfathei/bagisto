<?php

namespace Webkul\Saas\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Saas\Contracts\ContactInquiry as ContactInquiryContract;
use Webkul\Saas\Enums\ContactInquiryStatus;
use Webkul\Saas\Traits\HasUuid;

class ContactInquiry extends Model implements ContactInquiryContract
{
    use HasFactory, HasUuid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'saas_contact_inquiries';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'phone',
        'company',
        'inquiry_type',
        'subject',
        'message',
        'locale',
        'ip_address',
        'user_agent',
        'status',
        'handled_by',
        'handled_at',
        'notified_at',
        'admin_notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'status' => ContactInquiryStatus::class,
        'handled_at' => 'datetime',
        'notified_at' => 'datetime',
    ];

    /**
     * The Super Admin who last handled the inquiry.
     */
    public function handler(): BelongsTo
    {
        return $this->belongsTo(SuperAdminProxy::modelClass(), 'handled_by');
    }

    /**
     * Whether the inquiry still needs attention.
     */
    public function isOpen(): bool
    {
        return $this->status instanceof ContactInquiryStatus
            ? $this->status->isOpen()
            : $this->status !== ContactInquiryStatus::Resolved->value;
    }
}
