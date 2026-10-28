<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('roles') || ! Schema::hasTable('admins')) {
            return;
        }

        $allowed = array_values(array_unique((array) config('saas.tenant_role_permissions', [])));

        if ($allowed === []) {
            return;
        }

        $roleIds = DB::table('admins')
            ->whereNotNull('tenant_id')
            ->whereNotNull('role_id')
            ->distinct()
            ->pluck('role_id');

        if ($roleIds->isEmpty()) {
            return;
        }

        DB::table('roles')
            ->whereIn('id', $roleIds)
            ->where('permission_type', 'custom')
            ->chunkById(100, function ($roles) use ($allowed): void {
                foreach ($roles as $role) {
                    $permissions = json_decode((string) $role->permissions, true);

                    if (! is_array($permissions)) {
                        continue;
                    }

                    $filtered = array_values(array_intersect($permissions, $allowed));

                    if ($filtered === $permissions) {
                        continue;
                    }

                    DB::table('roles')->where('id', $role->id)->update([
                        'permissions' => json_encode($filtered),
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {}
};
