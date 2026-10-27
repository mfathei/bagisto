<?php

namespace Webkul\Saas\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Saas\Contracts\TenantInvitation;

class TenantInvitationRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return TenantInvitation::class;
    }

    /**
     * Pending invitations of a tenant.
     */
    public function pendingForTenant(int $tenantId)
    {
        return $this->model
            ->where('tenant_id', $tenantId)
            ->where('status', 'pending')
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->orderByDesc('created_at')
            ->get();
    }

    /**
     * Find a pending invitation by its token hash.
     */
    public function findByTokenHash(string $hash): ?TenantInvitation
    {
        return $this->model
            ->with('tenant')
            ->where('token_hash', $hash)
            ->where('status', 'pending')
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->first();
    }

    /**
     * Find a pending invitation for an email address within a tenant.
     */
    public function pendingForEmail(int $tenantId, string $email): ?TenantInvitation
    {
        return $this->model
            ->where('tenant_id', $tenantId)
            ->where('email', strtolower($email))
            ->where('status', 'pending')
            ->whereNull('revoked_at')
            ->first();
    }

    /**
     * Claim a pending invitation atomically, returning false when already used.
     */
    public function markAccepted(TenantInvitation $invitation): bool
    {
        return $this->model->newQuery()
            ->whereKey($invitation->getKey())
            ->where('status', 'pending')
            ->whereNull('accepted_at')
            ->whereNull('revoked_at')
            ->update([
                'status' => 'accepted',
                'accepted_at' => now(),
                'updated_at' => now(),
            ]) > 0;
    }
}
