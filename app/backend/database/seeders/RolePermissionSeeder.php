<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
            ['name' => 'admin.countries.index', 'guard_name' => $guardName, 'description' => 'List countries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.countries.create', 'guard_name' => $guardName, 'description' => 'Create countries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.countries.show', 'guard_name' => $guardName, 'description' => 'View countries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.countries.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit countries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.countries.update', 'guard_name' => $guardName, 'description' => 'Permission to update countries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.countries.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete countries', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.departments.index', 'guard_name' => $guardName, 'description' => 'List departments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.departments.create', 'guard_name' => $guardName, 'description' => 'Create departments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.departments.show', 'guard_name' => $guardName, 'description' => 'View departments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.departments.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit departments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.departments.update', 'guard_name' => $guardName, 'description' => 'Permission to update departments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.departments.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete departments', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.cities.index', 'guard_name' => $guardName, 'description' => 'List cities', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.cities.create', 'guard_name' => $guardName, 'description' => 'Create cities', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.cities.show', 'guard_name' => $guardName, 'description' => 'View cities', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.cities.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit cities', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.cities.update', 'guard_name' => $guardName, 'description' => 'Permission to update cities', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.cities.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete cities', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.neighborhoods.index', 'guard_name' => $guardName, 'description' => 'List neighborhoods', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.neighborhoods.create', 'guard_name' => $guardName, 'description' => 'Create neighborhoods', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.neighborhoods.show', 'guard_name' => $guardName, 'description' => 'View neighborhoods', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.neighborhoods.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit neighborhoods', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.neighborhoods.update', 'guard_name' => $guardName, 'description' => 'Permission to update neighborhoods', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.neighborhoods.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete neighborhoods', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.categories.index', 'guard_name' => $guardName, 'description' => 'List categories', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.categories.create', 'guard_name' => $guardName, 'description' => 'Create categories', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.categories.show', 'guard_name' => $guardName, 'description' => 'View categories', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.categories.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit categories', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.categories.update', 'guard_name' => $guardName, 'description' => 'Permission to update categories', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.categories.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete categories', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.banks.index', 'guard_name' => $guardName, 'description' => 'List banks', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.banks.create', 'guard_name' => $guardName, 'description' => 'Create banks', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.banks.show', 'guard_name' => $guardName, 'description' => 'View banks', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.banks.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit banks', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.banks.update', 'guard_name' => $guardName, 'description' => 'Permission to update banks', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.banks.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete banks', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.support_statuses.index', 'guard_name' => $guardName, 'description' => 'List support statuses', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.support_statuses.create', 'guard_name' => $guardName, 'description' => 'Create support statuses', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.support_statuses.show', 'guard_name' => $guardName, 'description' => 'View support statuses', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.support_statuses.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit support statuses', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.support_statuses.update', 'guard_name' => $guardName, 'description' => 'Permission to update support statuses', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.support_statuses.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete support statuses', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.establishments.index', 'guard_name' => $guardName, 'description' => 'List establishments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.establishments.create', 'guard_name' => $guardName, 'description' => 'Create establishments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.establishments.show', 'guard_name' => $guardName, 'description' => 'View establishments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.establishments.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit establishments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.establishments.update', 'guard_name' => $guardName, 'description' => 'Permission to update establishments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.establishments.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete establishments', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.roles.index', 'guard_name' => $guardName, 'description' => 'List roles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.roles.create', 'guard_name' => $guardName, 'description' => 'Create roles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.roles.show', 'guard_name' => $guardName, 'description' => 'View roles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.roles.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit roles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.roles.update', 'guard_name' => $guardName, 'description' => 'Permission to update roles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.roles.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete roles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.roles.assign_permissions', 'guard_name' => $guardName, 'description' => 'Permission to assign permissions to roles', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.permissions.index', 'guard_name' => $guardName, 'description' => 'List permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.permissions.create', 'guard_name' => $guardName, 'description' => 'Create permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.permissions.show', 'guard_name' => $guardName, 'description' => 'View permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.permissions.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.permissions.update', 'guard_name' => $guardName, 'description' => 'Permission to update permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.permissions.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete permissions', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.pqrs_types.index', 'guard_name' => $guardName, 'description' => 'List PQRs types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.pqrs_types.create', 'guard_name' => $guardName, 'description' => 'Create PQRs types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.pqrs_types.show', 'guard_name' => $guardName, 'description' => 'View PQRs types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.pqrs_types.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit PQRs types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.pqrs_types.update', 'guard_name' => $guardName, 'description' => 'Permission to update PQRs types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.pqrs_types.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete PQRs types', 'created_at' => now(), 'updated_at' => now()],

            // Users
            ['name' => 'admin.users.index', 'guard_name' => $guardName, 'description' => 'List permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.users.create', 'guard_name' => $guardName, 'description' => 'Create permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.users.show', 'guard_name' => $guardName, 'description' => 'View permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.users.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.users.update', 'guard_name' => $guardName, 'description' => 'Permission to update permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.users.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.users.assign_roles_permissions', 'guard_name' => $guardName, 'description' => 'Permission to assign roles and permissions to users', 'created_at' => now(), 'updated_at' => now()],

            // Administrator Modules
            ['name' => 'admin.profiles.view', 'guard_name' => $guardName, 'description' => 'View admin profiles module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.create', 'guard_name' => $guardName, 'description' => 'Create admin profiles module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.parametrization.view', 'guard_name' => $guardName, 'description' => 'View admin parametrization module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.provider_validate.view', 'guard_name' => $guardName, 'description' => 'View admin provider validate module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.marketing.view', 'guard_name' => $guardName, 'description' => 'View admin marketing module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.dashboard.view', 'guard_name' => $guardName, 'description' => 'View admin dashboard module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.support.view', 'guard_name' => $guardName, 'description' => 'View admin support module', 'created_at' => now(), 'updated_at' => now()],

            // PQRS Modules
            ['name' => 'admin.my_pqrs.view', 'guard_name' => $guardName, 'description' => 'View admin My PQRs module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.manage_pqrs.view', 'guard_name' => $guardName, 'description' => 'View admin manage PQRs module', 'created_at' => now(), 'updated_at' => now()],

            // Provider Modules
            ['name' => 'provider.basic_data.view', 'guard_name' => $guardName, 'description' => 'View provider basic data module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.commerces.view', 'guard_name' => $guardName, 'description' => 'View provider commerces module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.products.view', 'guard_name' => $guardName, 'description' => 'View provider products module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.my_account.view', 'guard_name' => $guardName, 'description' => 'View provider my account module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.my_wallet.view', 'guard_name' => $guardName, 'description' => 'View provider my wallet module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.dashboard.view', 'guard_name' => $guardName, 'description' => 'View provider dashboard module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.review.view', 'guard_name' => $guardName, 'description' => 'View provider review module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.support.view', 'guard_name' => $guardName, 'description' => 'View provider support module', 'created_at' => now(), 'updated_at' => now()],

            // Commerces Modules
            ['name' => 'provider.commerces.index', 'guard_name' => $guardName, 'description' => 'List commerces', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.commerces.create', 'guard_name' => $guardName, 'description' => 'Create commerces', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.commerces.show', 'guard_name' => $guardName, 'description' => 'View commerces', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.commerces.view', 'guard_name' => $guardName, 'description' => 'View commerces', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.commerces.edit', 'guard_name' => $guardName, 'description' => 'Edit commerces', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.commerces.update', 'guard_name' => $guardName, 'description' => 'Update commerces', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.commerces.delete', 'guard_name' => $guardName, 'description' => 'Delete commerces', 'created_at' => now(), 'updated_at' => now()],

            // Legal Representatives
            ['name' => 'provider.legal_representatives.index', 'guard_name' => $guardName, 'description' => 'List legal representatives', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.legal_representatives.create', 'guard_name' => $guardName, 'description' => 'Create legal representatives', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.legal_representatives.show', 'guard_name' => $guardName, 'description' => 'View legal representatives', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.legal_representatives.view', 'guard_name' => $guardName, 'description' => 'View legal representatives', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.legal_representatives.edit', 'guard_name' => $guardName, 'description' => 'Edit legal representatives', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.legal_representatives.update', 'guard_name' => $guardName, 'description' => 'Update legal representatives', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.legal_representatives.delete', 'guard_name' => $guardName, 'description' => 'Delete legal representatives', 'created_at' => now(), 'updated_at' => now()],

            // Establishment Types
            ['name' => 'provider.establishment_types.index', 'guard_name' => $guardName, 'description' => 'List establishment types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.establishment_types.create', 'guard_name' => $guardName, 'description' => 'Create establishment types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.establishment_types.show', 'guard_name' => $guardName, 'description' => 'View establishment types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.establishment_types.view', 'guard_name' => $guardName, 'description' => 'View establishment types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.establishment_types.edit', 'guard_name' => $guardName, 'description' => 'Edit establishment types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.establishment_types.update', 'guard_name' => $guardName, 'description' => 'Update establishment types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.establishment_types.delete', 'guard_name' => $guardName, 'description' => 'Delete establishment types', 'created_at' => now(), 'updated_at' => now()],
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
