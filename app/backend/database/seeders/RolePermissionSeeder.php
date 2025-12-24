<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar caché de permisos        
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $guardName = 'sanctum';

        // Crear permisos solo si no existen
        $permissions = [

            // Parametrization
            ['name' => 'countries.index', 'guard_name' => $guardName, 'description' => 'List countries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'countries.create', 'guard_name' => $guardName, 'description' => 'Create countries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'countries.show', 'guard_name' => $guardName, 'description' => 'View countries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'countries.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit countries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'countries.update', 'guard_name' => $guardName, 'description' => 'Permission to update countries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'countries.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete countries', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'departments.index', 'guard_name' => $guardName, 'description' => 'List departments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'departments.create', 'guard_name' => $guardName, 'description' => 'Create departments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'departments.show', 'guard_name' => $guardName, 'description' => 'View departments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'departments.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit departments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'departments.update', 'guard_name' => $guardName, 'description' => 'Permission to update departments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'departments.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete departments', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'cities.index', 'guard_name' => $guardName, 'description' => 'List cities', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'cities.create', 'guard_name' => $guardName, 'description' => 'Create cities', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'cities.show', 'guard_name' => $guardName, 'description' => 'View cities', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'cities.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit cities', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'cities.update', 'guard_name' => $guardName, 'description' => 'Permission to update cities', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'cities.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete cities', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'neighborhoods.index', 'guard_name' => $guardName, 'description' => 'List neighborhoods', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'neighborhoods.create', 'guard_name' => $guardName, 'description' => 'Create neighborhoods', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'neighborhoods.show', 'guard_name' => $guardName, 'description' => 'View neighborhoods', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'neighborhoods.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit neighborhoods', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'neighborhoods.update', 'guard_name' => $guardName, 'description' => 'Permission to update neighborhoods', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'neighborhoods.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete neighborhoods', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'categories.index', 'guard_name' => $guardName, 'description' => 'List categories', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'categories.create', 'guard_name' => $guardName, 'description' => 'Create categories', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'categories.show', 'guard_name' => $guardName, 'description' => 'View categories', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'categories.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit categories', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'categories.update', 'guard_name' => $guardName, 'description' => 'Permission to update categories', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'categories.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete categories', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'establishments.index', 'guard_name' => $guardName, 'description' => 'List establishments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'establishments.create', 'guard_name' => $guardName, 'description' => 'Create establishments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'establishments.show', 'guard_name' => $guardName, 'description' => 'View establishments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'establishments.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit establishments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'establishments.update', 'guard_name' => $guardName, 'description' => 'Permission to update establishments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'establishments.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete establishments', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'roles.index', 'guard_name' => $guardName, 'description' => 'List roles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'roles.create', 'guard_name' => $guardName, 'description' => 'Create roles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'roles.show', 'guard_name' => $guardName, 'description' => 'View roles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'roles.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit roles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'roles.update', 'guard_name' => $guardName, 'description' => 'Permission to update roles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'roles.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete roles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'roles.assign_permissions', 'guard_name' => $guardName, 'description' => 'Permission to assign permissions to roles', 'created_at' => now(), 'updated_at' => now()],
            
            ['name' => 'permissions.index', 'guard_name' => $guardName, 'description' => 'List permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'permissions.create', 'guard_name' => $guardName, 'description' => 'Create permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'permissions.show', 'guard_name' => $guardName, 'description' => 'View permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'permissions.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'permissions.update', 'guard_name' => $guardName, 'description' => 'Permission to update permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'permissions.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete permissions', 'created_at' => now(), 'updated_at' => now()],

            // Users
            ['name' => 'users.index', 'guard_name' => $guardName, 'description' => 'List permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'users.create', 'guard_name' => $guardName, 'description' => 'Create permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'users.show', 'guard_name' => $guardName, 'description' => 'View permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'users.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'users.update', 'guard_name' => $guardName, 'description' => 'Permission to update permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'users.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'users.assign_roles_permissions', 'guard_name' => $guardName, 'description' => 'Permission to assign roles and permissions to users', 'created_at' => now(), 'updated_at' => now()],

            // Administrator Modules
            ['name' => 'admin_profiles.view', 'guard_name' => $guardName, 'description' => 'View admin profiles module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin_parametrization.view', 'guard_name' => $guardName, 'description' => 'View admin parametrization module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin_provider_validate.view', 'guard_name' => $guardName, 'description' => 'View admin provider validate module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin_marketing.view', 'guard_name' => $guardName, 'description' => 'View admin marketing module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin_dashboard.view', 'guard_name' => $guardName, 'description' => 'View admin dashboard module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin_support.view', 'guard_name' => $guardName, 'description' => 'View admin support module', 'created_at' => now(), 'updated_at' => now()],

            // PQRS Modules
            ['name' => 'admin_my_pqrs.view', 'guard_name' => $guardName, 'description' => 'View admin My PQRs module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin_manage_pqrs.view', 'guard_name' => $guardName, 'description' => 'View admin manage PQRs module', 'created_at' => now(), 'updated_at' => now()],

            // Provider Modules
            ['name' => 'provider_basic_data.view', 'guard_name' => $guardName, 'description' => 'View provider basic data module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider_commerces.view', 'guard_name' => $guardName, 'description' => 'View provider commerces module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider_products.view', 'guard_name' => $guardName, 'description' => 'View provider products module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider_my_account.view', 'guard_name' => $guardName, 'description' => 'View provider my account module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider_my_wallet.view', 'guard_name' => $guardName, 'description' => 'View provider my wallet module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider_dashboard.view', 'guard_name' => $guardName, 'description' => 'View provider dashboard module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider_review.view', 'guard_name' => $guardName, 'description' => 'View provider review module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider_support.view', 'guard_name' => $guardName, 'description' => 'View provider support module', 'created_at' => now(), 'updated_at' => now()],

            // Commerces Modules
            ['name' => 'commerces.index', 'guard_name' => $guardName, 'description' => 'List commerces', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'commerces.create', 'guard_name' => $guardName, 'description' => 'Create commerces', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'commerces.show', 'guard_name' => $guardName, 'description' => 'View commerces', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'commerces.view', 'guard_name' => $guardName, 'description' => 'View commerces', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'commerces.edit', 'guard_name' => $guardName, 'description' => 'Edit commerces', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'commerces.update', 'guard_name' => $guardName, 'description' => 'Update commerces', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'commerces.delete', 'guard_name' => $guardName, 'description' => 'Delete commerces', 'created_at' => now(), 'updated_at' => now()],

            // Legal Representatives
            ['name' => 'legal_representatives.index', 'guard_name' => $guardName, 'description' => 'List legal representatives', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'legal_representatives.create', 'guard_name' => $guardName, 'description' => 'Create legal representatives', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'legal_representatives.show', 'guard_name' => $guardName, 'description' => 'View legal representatives', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'legal_representatives.view', 'guard_name' => $guardName, 'description' => 'View legal representatives', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'legal_representatives.edit', 'guard_name' => $guardName, 'description' => 'Edit legal representatives', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'legal_representatives.update', 'guard_name' => $guardName, 'description' => 'Update legal representatives', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'legal_representatives.delete', 'guard_name' => $guardName, 'description' => 'Delete legal representatives', 'created_at' => now(), 'updated_at' => now()],

            // Establishment Types
            ['name' => 'establishment_types.index', 'guard_name' => $guardName, 'description' => 'List establishment types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'establishment_types.create', 'guard_name' => $guardName, 'description' => 'Create establishment types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'establishment_types.show', 'guard_name' => $guardName, 'description' => 'View establishment types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'establishment_types.view', 'guard_name' => $guardName, 'description' => 'View establishment types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'establishment_types.edit', 'guard_name' => $guardName, 'description' => 'Edit establishment types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'establishment_types.update', 'guard_name' => $guardName, 'description' => 'Update establishment types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'establishment_types.delete', 'guard_name' => $guardName, 'description' => 'Delete establishment types', 'created_at' => now(), 'updated_at' => now()],
        ];

        // Crear permisos si no existen
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission['name'], 'guard_name' => $permission['guard_name']],
                $permission
            );
        }

        // Crear roles si no existen
        $roles = [
            ['name' => 'superadmin', 'guard_name' => $guardName, 'description' => 'Super Administrator role'],
            ['name' => 'admin', 'guard_name' => $guardName, 'description' => 'Administrator role'],
            ['name' => 'provider', 'guard_name' => $guardName, 'description' => 'Provider role'],
            ['name' => 'user', 'guard_name' => $guardName, 'description' => 'User role'],
        ];

        foreach ($roles as $roleData) {
            Role::firstOrCreate(
                ['name' => $roleData['name'], 'guard_name' => $roleData['guard_name']],
                $roleData
            );
        }

        // Asignar permisos a roles
        $role = Role::where('name', 'superadmin')->first();        
        $role->givePermissionTo(Permission::all());

        // Asignar rol a un usuario específico
        $user = User::first();
        $user->assignRole('superadmin'); 
    }
}
