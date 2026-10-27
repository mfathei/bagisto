<?php

namespace Webkul\Saas\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Webkul\Saas\Contracts\DomainChallenge as DomainChallengeContract;
use Webkul\Saas\Enums\ChallengeStatus;
use Webkul\Saas\Enums\ChallengeType;
use Webkul\Saas\Traits\HasUuid;

class DomainChallenge extends Model implements DomainChallengeContract
{
    use HasFactory, HasUuid;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'saas_domain_challenges';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'uuid',
        'tenant_domain_id',
        'type',
        'token',
        'expected_value',
        'status',
        'attempts',
        'last_checked_at',
        'verified_at',
        'expires_at',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'type' => ChallengeType::class,
        'status' => ChallengeStatus::class,
        'attempts' => 'integer',
        'last_checked_at' => 'datetime',
        'verified_at' => 'datetime',
        'expires_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * The domain being verified.
     */
    public function domain(): BelongsTo
    {
        return $this->belongsTo(TenantDomainProxy::modelClass(), 'tenant_domain_id');
    }
}
