<?php

namespace Webkul\Saas\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Saas\Contracts\TenantUser as TenantUserContract;
use Webkul\Saas\Enums\MembershipRole;
use Webkul\Saas\Enums\MembershipStatus;
use Webkul\Saas\Traits\HasUuid;
use Webkul\User\Models\AdminProxy;

class TenantUser extends Model implements TenantUserContract
{
    use HasFactory, HasUuid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'saas_tenant_users';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'uuid',
        'tenant_id',
        'admin_id',
        'role',
        'status',
        'invited_by',
        'invited_at',
        'accepted_at',
        'email_verified_at',
        'verification_token',
        'verification_token_expires_at',
        'last_active_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'role' => MembershipRole::class,
        'status' => MembershipStatus::class,
        'invited_at' => 'datetime',
        'accepted_at' => 'datetime',
        'email_verified_at' => 'datetime',
        'verification_token_expires_at' => 'datetime',
        'last_active_at' => 'datetime',
    ];

    /**
     * The tenant of the membership.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantProxy::modelClass());
    }

    /**
     * The Bagisto admin account of the member.
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(AdminProxy::modelClass(), 'admin_id');
    }

    /**
     * The admin who invited the member.
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(AdminProxy::modelClass(), 'invited_by');
    }

    /**
     * Whether the membership is active and verified.
     */
    public function isActive(): bool
    {
        return $this->status === MembershipStatus::Active;
    }
}
