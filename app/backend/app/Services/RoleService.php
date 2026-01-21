<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\Constant;
use App\Models\Role;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Permission;

class RoleService
{
    /**
     * Get paginated roles with permissions and user count.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    /**
     * Obtiene roles paginados con filtros opcionales por nombre, descripción y permiso, incluyendo permisos y conteo de usuarios.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginatedWithPermissionsAndUserCount(array $filters = [])
    {
        $query = Role::with('permissions');
        $perPage = $filters['per_page'] ?? Constant::DEFAULT_PER_PAGE;

        if (! empty($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }
        if (! empty($filters['description'])) {
            $query->where('description', 'like', "%{$filters['description']}%");
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['permission'])) {
            $query->whereHas('permissions', function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['permission']}%");
            });
        }
        if (! empty($filters['q'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', "%{$filters['q']}%")
                    ->orWhere('description', 'like', "%{$filters['q']}%");
            });
        }

        $roles = $query->paginate($perPage);

        // Agregar el conteo de usuarios a cada rol
        $roles->getCollection()->transform(function ($role) {
            $role->user_count = $role->users()->count();

            return $role;
        });

        return $roles;
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

    /**
     * Update the status of a role.
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    public function updateStatus(int $roleId, int $status): Role
    {
        try {
            $role = Role::findOrFail($roleId);
            $role->status = $status;
            $role->save();

            return $role;
        } catch (Exception $e) {
            Log::error('Error updating role status', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
