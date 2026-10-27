<?php

namespace Webkul\Saas\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Saas\Contracts\TenantInvitation as TenantInvitationContract;
use Webkul\Saas\Enums\MembershipRole;
use Webkul\Saas\Traits\HasUuid;
use Webkul\User\Models\AdminProxy;

class TenantInvitation extends Model implements TenantInvitationContract
{
    use HasFactory, HasUuid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'saas_tenant_invitations';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'uuid',
        'tenant_id',
        'email',
        'role',
        'status',
        'token_hash',
        'invited_by',
        'expires_at',
        'accepted_at',
        'revoked_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'role' => MembershipRole::class,
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    /**
     * The tenant the invitation belongs to.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantProxy::modelClass());
    }

    /**
     * The admin who issued the invitation.
     */
    public function inviter(): BelongsTo
    {
        return $this->belongsTo(AdminProxy::modelClass(), 'invited_by');
    }

    /**
     * Whether the invitation can still be accepted.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending'
            && ! $this->accepted_at
            && ! $this->revoked_at
            && (! $this->expires_at || $this->expires_at->isFuture());
    }
}
