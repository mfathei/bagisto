<?php

namespace Webkul\Saas\Repositories;

use Webkul\Core\Eloquent\Repository;
use Webkul\Saas\Contracts\SuperAdmin;

class SuperAdminRepository extends Repository
{
    /**
     * Specify model class name.
     */
    public function model(): string
    {
        return SuperAdmin::class;
    }

    /**
     * Find a super admin by email address.
     */
    public function findByEmail(string $email): ?SuperAdmin
    {
        return $this->model->where('email', strtolower($email))->first();
    }

    /**
     * Whether any super admin account exists.
     */
    public function exists(): bool
    {
        return $this->model->newQuery()->exists();
    }
}
