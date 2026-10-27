<?php

namespace Webkul\Saas\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Webkul\Saas\Contracts\TenantDomain as TenantDomainContract;
use Webkul\Saas\Enums\DomainStatus;
use Webkul\Saas\Enums\DomainType;
use Webkul\Saas\Enums\SslStatus;
use Webkul\Saas\Traits\HasUuid;

class TenantDomain extends Model implements TenantDomainContract
{
    use HasFactory, HasUuid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'saas_tenant_domains';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'uuid',
        'tenant_id',
        'hostname',
        'type',
        'is_primary',
        'status',
        'verification_token',
        'verification_token_expires_at',
        'verified_at',
        'ssl_status',
        'ssl_issued_at',
        'dns_target',
        'last_checked_at',
        'failure_reason',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => DomainType::class,
        'status' => DomainStatus::class,
        'ssl_status' => SslStatus::class,
        'is_primary' => 'boolean',
        'verification_token_expires_at' => 'datetime',
        'verified_at' => 'datetime',
        'ssl_issued_at' => 'datetime',
        'last_checked_at' => 'datetime',
    ];

    /**
     * The tenant owning the domain.
     */
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(TenantProxy::modelClass());
    }

    /**
     * Verification challenges issued for the domain.
     */
    public function challenges(): HasMany
    {
        return $this->hasMany(DomainChallengeProxy::modelClass());
    }

    /**
     * Whether the hostname is verified and enabled.
     */
    public function isServable(): bool
    {
        return $this->status === DomainStatus::Verified;
    }

    /**
     * Whether the domain is a customer-owned hostname.
     */
    public function isCustom(): bool
    {
        return $this->type === DomainType::Custom;
    }
}
