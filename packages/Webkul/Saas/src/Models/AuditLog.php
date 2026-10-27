<?php

namespace Webkul\Saas\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Saas\Contracts\AuditLog as AuditLogContract;
use Webkul\Saas\Traits\HasUuid;

class AuditLog extends Model implements AuditLogContract
{
    use HasFactory, HasUuid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'saas_audit_logs';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'uuid',
        'tenant_id',
        'actor_type',
        'actor_id',
        'actor_name',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'metadata',
        'ip_address',
        'user_agent',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * The tenant the event belongs to, when applicable.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantProxy::modelClass());
    }
}
