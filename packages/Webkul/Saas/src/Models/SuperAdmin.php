<?php

namespace Webkul\Saas\Models;

use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Webkul\Saas\Contracts\SuperAdmin as SuperAdminContract;

class SuperAdmin extends Authenticatable implements CanResetPasswordContract, SuperAdminContract
{
    use CanResetPassword, HasFactory, Notifiable;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'saas_super_admins';

    /**
     * The attributes that are mass assignable.
     *
     * @var string[]
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden.
     *
     * @var string[]
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'password' => 'hashed',
        'status' => 'boolean',
        'last_login_at' => 'datetime',
    ];

    /**
     * Whether the account holds full platform authority.
     */
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    /**
     * Whether the account is read-only platform support.
     */
    public function isSupport(): bool
    {
        return $this->role === 'support';
    }

    /**
     * Whether the account may perform the given platform ability.
     */
    public function hasAbility(string $ability): bool
    {
        if (in_array($this->role, ['owner', 'admin'], true)) {
            return true;
        }

        return in_array($ability, [
            'tenants.view',
            'audit.view',
            'dashboard.view',
            'inquiries.view',
            'inquiries.manage',
        ], true);
    }
}
