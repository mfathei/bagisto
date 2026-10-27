<?php

namespace Webkul\Saas\Services;

use Webkul\Saas\Contracts\Tenant;
use Webkul\User\Contracts\Role;
use Webkul\User\Repositories\RoleRepository;

class TenantRoleService
{
    /**
     * Create a new tenant role service.
     */
    public function __construct(protected RoleRepository $roles) {}

    /**
     * Ensure the restricted Bagisto role shared by the tenant staff exists.
     */
    public function ensure(Tenant $tenant): Role
    {
        $name = 'Tenant role '.$tenant->uuid;

        $permissions = array_values(array_unique((array) config('saas.tenant_role_permissions', [])));

        $role = $this->roles->findWhere(['name' => $name])->first();

        if ($role) {
            $role->forceFill([
                'permission_type' => 'custom',
                'permissions' => $permissions,
            ])->save();

            return $role;
        }

        return $this->roles->create([
            'name' => $name,
            'description' => 'Restricted administrator role for '.$tenant->name.'.',
            'permission_type' => 'custom',
            'permissions' => $permissions,
        ]);
    }
}
