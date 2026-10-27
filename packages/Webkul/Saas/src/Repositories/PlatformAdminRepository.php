<?php

namespace Webkul\Saas\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\User\Contracts\Admin;

class PlatformAdminRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return Admin::class;
    }

    /**
     * Find an admin by email across every tenant.
     */
    public function findByEmail(string $email): ?Admin
    {
        return $this->getModel()
            ->withoutGlobalScopes()
            ->where('email', strtolower($email))
            ->first();
    }
}
