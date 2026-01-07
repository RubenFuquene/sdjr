<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Constants\Constant;
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
            ['name' => 'admin.params.countries.index', 'guard_name' => $guardName, 'description' => 'List countries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.countries.create', 'guard_name' => $guardName, 'description' => 'Create countries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.countries.show', 'guard_name' => $guardName, 'description' => 'View countries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.countries.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit countries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.countries.update', 'guard_name' => $guardName, 'description' => 'Permission to update countries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.countries.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete countries', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.params.departments.index', 'guard_name' => $guardName, 'description' => 'List departments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.departments.create', 'guard_name' => $guardName, 'description' => 'Create departments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.departments.show', 'guard_name' => $guardName, 'description' => 'View departments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.departments.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit departments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.departments.update', 'guard_name' => $guardName, 'description' => 'Permission to update departments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.departments.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete departments', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.params.cities.index', 'guard_name' => $guardName, 'description' => 'List cities', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.cities.create', 'guard_name' => $guardName, 'description' => 'Create cities', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.cities.show', 'guard_name' => $guardName, 'description' => 'View cities', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.cities.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit cities', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.cities.update', 'guard_name' => $guardName, 'description' => 'Permission to update cities', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.cities.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete cities', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.params.neighborhoods.index', 'guard_name' => $guardName, 'description' => 'List neighborhoods', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.neighborhoods.create', 'guard_name' => $guardName, 'description' => 'Create neighborhoods', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.neighborhoods.show', 'guard_name' => $guardName, 'description' => 'View neighborhoods', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.neighborhoods.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit neighborhoods', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.neighborhoods.update', 'guard_name' => $guardName, 'description' => 'Permission to update neighborhoods', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.neighborhoods.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete neighborhoods', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.params.categories.index', 'guard_name' => $guardName, 'description' => 'List categories', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.categories.create', 'guard_name' => $guardName, 'description' => 'Create categories', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.categories.show', 'guard_name' => $guardName, 'description' => 'View categories', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.categories.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit categories', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.categories.update', 'guard_name' => $guardName, 'description' => 'Permission to update categories', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.categories.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete categories', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.params.banks.index', 'guard_name' => $guardName, 'description' => 'List banks', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.banks.create', 'guard_name' => $guardName, 'description' => 'Create banks', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.banks.show', 'guard_name' => $guardName, 'description' => 'View banks', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.banks.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit banks', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.banks.update', 'guard_name' => $guardName, 'description' => 'Permission to update banks', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.banks.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete banks', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.params.support_statuses.index', 'guard_name' => $guardName, 'description' => 'List support statuses', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.support_statuses.create', 'guard_name' => $guardName, 'description' => 'Create support statuses', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.support_statuses.show', 'guard_name' => $guardName, 'description' => 'View support statuses', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.support_statuses.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit support statuses', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.support_statuses.update', 'guard_name' => $guardName, 'description' => 'Permission to update support statuses', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.support_statuses.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete support statuses', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.params.establishments.index', 'guard_name' => $guardName, 'description' => 'List establishments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.establishments.create', 'guard_name' => $guardName, 'description' => 'Create establishments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.establishments.show', 'guard_name' => $guardName, 'description' => 'View establishments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.establishments.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit establishments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.establishments.update', 'guard_name' => $guardName, 'description' => 'Permission to update establishments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.establishments.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete establishments', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.params.pqrs_types.index', 'guard_name' => $guardName, 'description' => 'List PQRs types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.pqrs_types.create', 'guard_name' => $guardName, 'description' => 'Create PQRs types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.pqrs_types.show', 'guard_name' => $guardName, 'description' => 'View PQRs types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.pqrs_types.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit PQRs types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.pqrs_types.update', 'guard_name' => $guardName, 'description' => 'Permission to update PQRs types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.pqrs_types.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete PQRs types', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.params.priority_types.index', 'guard_name' => $guardName, 'description' => 'List priority types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.priority_types.create', 'guard_name' => $guardName, 'description' => 'Create priority types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.priority_types.show', 'guard_name' => $guardName, 'description' => 'View priority types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.priority_types.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit priority types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.priority_types.update', 'guard_name' => $guardName, 'description' => 'Permission to update priority types', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.priority_types.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete priority types', 'created_at' => now(), 'updated_at' => now()],

            // Profiles Management
            ['name' => 'admin.profiles.roles.index', 'guard_name' => $guardName, 'description' => 'List roles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.roles.create', 'guard_name' => $guardName, 'description' => 'Create roles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.roles.show', 'guard_name' => $guardName, 'description' => 'View roles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.roles.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit roles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.roles.update', 'guard_name' => $guardName, 'description' => 'Permission to update roles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.roles.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete roles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.roles.assign_permissions', 'guard_name' => $guardName, 'description' => 'Permission to assign permissions to roles', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.profiles.permissions.index', 'guard_name' => $guardName, 'description' => 'List permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.permissions.create', 'guard_name' => $guardName, 'description' => 'Create permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.permissions.show', 'guard_name' => $guardName, 'description' => 'View permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.permissions.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.permissions.update', 'guard_name' => $guardName, 'description' => 'Permission to update permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.permissions.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete permissions', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.profiles.provider.index', 'guard_name' => $guardName, 'description' => 'List provider', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.provider.create', 'guard_name' => $guardName, 'description' => 'Create provider', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.provider.show', 'guard_name' => $guardName, 'description' => 'View provider', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.provider.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit provider', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.provider.update', 'guard_name' => $guardName, 'description' => 'Permission to update provider', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.provider.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete provider', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.profiles.users.index', 'guard_name' => $guardName, 'description' => 'List permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.users.create', 'guard_name' => $guardName, 'description' => 'Create permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.users.show', 'guard_name' => $guardName, 'description' => 'View permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.users.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.users.update', 'guard_name' => $guardName, 'description' => 'Permission to update permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.users.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete permissions', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.users.assign_roles_permissions', 'guard_name' => $guardName, 'description' => 'Permission to assign roles and permissions to users', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.users.update_status', 'guard_name' => $guardName, 'description' => 'Permission to update user status', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.profiles.admin.index', 'guard_name' => $guardName, 'description' => 'List admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.admin.create', 'guard_name' => $guardName, 'description' => 'Create admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.admin.show', 'guard_name' => $guardName, 'description' => 'View admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.admin.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.admin.update', 'guard_name' => $guardName, 'description' => 'Permission to update admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.admin.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete admin', 'created_at' => now(), 'updated_at' => now()],

            // Administrator Modules
            ['name' => 'admin.profiles.view', 'guard_name' => $guardName, 'description' => 'View admin profiles module', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.view', 'guard_name' => $guardName, 'description' => 'View admin parametrization module', 'created_at' => now(), 'updated_at' => now()],
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
            ['name' => 'superadmin', 'guard_name' => $guardName, 'description' => 'Super Administrator role', 'status' => Constant::STATUS_ACTIVE],
            ['name' => 'admin', 'guard_name' => $guardName, 'description' => 'Administrator role', 'status' => Constant::STATUS_ACTIVE],
            ['name' => 'provider', 'guard_name' => $guardName, 'description' => 'Provider role', 'status' => Constant::STATUS_ACTIVE],
            ['name' => 'user', 'guard_name' => $guardName, 'description' => 'User role', 'status' => Constant::STATUS_ACTIVE],
            ['name' => 'guest', 'guard_name' => $guardName, 'description' => 'User role', 'status' => Constant::STATUS_INACTIVE],
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
