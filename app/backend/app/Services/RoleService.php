<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleService
{
    /**
     * Get paginated roles with permissions and user count.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginatedWithPermissionsAndUserCount(int $perPage = 15)
    {
        return Role::with('permissions')
            ->paginate($perPage);
    }

    /**
     * Create a new role and assign permissions.
     *
     * @throws Exception
     */
    public function createRole(string $name, string $description, array $permissions = []): Role
    {
        DB::beginTransaction();
        try {
            $role = Role::create([
                'name' => $name,
                'description' => $description,
            ]);
            if (! empty($permissions)) {
                $role->syncPermissions($permissions);
            }
            DB::commit();

            return $role;
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating role', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Create a new permission.
     *
     * @throws Exception
     */
    public function createPermission(string $name, string $description): Permission
    {
        try {
            return Permission::create([
                'name' => $name,
                'description' => $description,
            ]);
        } catch (Exception $e) {
            Log::error('Error creating permission', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Assign roles and permissions to a user.
     *
     * @throws Exception
     */
    /**
     * Assign roles and permissions to a user, with sync or give option.
     *
     * @throws Exception
     */
    public function assignToUser(User $user, array $roles = [], array $permissions = [], bool $sync = true): void
    {
        DB::beginTransaction();
        try {
            if (! empty($roles)) {
                $sync ? $user->syncRoles($roles) : $user->assignRole($roles);
            }
            if (! empty($permissions)) {
                $sync ? $user->syncPermissions($permissions) : $user->givePermissionTo($permissions);
            }
            DB::commit();
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error assigning roles/permissions', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Assign permissions to a role.
     *
     * @throws Exception
     */
    /**
     * Assign permissions to a role, with sync or give option.
     *
     * @throws Exception
     */
    public function assignPermissionsToRole(Role $role, array $permissions, bool $sync = true): void
    {
        try {
            $sync ? $role->syncPermissions($permissions) : $role->givePermissionTo($permissions);
        } catch (Exception $e) {
            Log::error('Error assigning permissions to role', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
