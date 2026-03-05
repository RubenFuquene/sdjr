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

            // Parametrización
            ['name' => 'admin.params.countries.index', 'guard_name' => $guardName, 'description' => 'Listar países', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.countries.create', 'guard_name' => $guardName, 'description' => 'Crear países', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.countries.show', 'guard_name' => $guardName, 'description' => 'Ver países', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.countries.update', 'guard_name' => $guardName, 'description' => 'Permiso para actualizar países', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.countries.delete', 'guard_name' => $guardName, 'description' => 'Permiso para eliminar países', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.params.departments.index', 'guard_name' => $guardName, 'description' => 'Listar departamentos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.departments.create', 'guard_name' => $guardName, 'description' => 'Crear departamentos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.departments.show', 'guard_name' => $guardName, 'description' => 'Ver departamentos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.departments.update', 'guard_name' => $guardName, 'description' => 'Permiso para actualizar departamentos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.departments.delete', 'guard_name' => $guardName, 'description' => 'Permiso para eliminar departamentos', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.params.cities.index', 'guard_name' => $guardName, 'description' => 'Listar ciudades', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.cities.create', 'guard_name' => $guardName, 'description' => 'Crear ciudades', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.cities.show', 'guard_name' => $guardName, 'description' => 'Ver ciudades', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.cities.update', 'guard_name' => $guardName, 'description' => 'Permiso para actualizar ciudades', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.cities.delete', 'guard_name' => $guardName, 'description' => 'Permiso para eliminar ciudades', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.params.neighborhoods.index', 'guard_name' => $guardName, 'description' => 'Listar barrios', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.neighborhoods.create', 'guard_name' => $guardName, 'description' => 'Crear barrios', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.neighborhoods.show', 'guard_name' => $guardName, 'description' => 'Ver barrios', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.neighborhoods.update', 'guard_name' => $guardName, 'description' => 'Permiso para actualizar barrios', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.neighborhoods.delete', 'guard_name' => $guardName, 'description' => 'Permiso para eliminar barrios', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.params.categories.index', 'guard_name' => $guardName, 'description' => 'Listar categorías', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.categories.create', 'guard_name' => $guardName, 'description' => 'Crear categorías', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.categories.show', 'guard_name' => $guardName, 'description' => 'Ver categorías', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.categories.update', 'guard_name' => $guardName, 'description' => 'Permiso para actualizar categorías', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.categories.delete', 'guard_name' => $guardName, 'description' => 'Permiso para eliminar categorías', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.params.banks.index', 'guard_name' => $guardName, 'description' => 'Listar bancos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.banks.create', 'guard_name' => $guardName, 'description' => 'Crear bancos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.banks.show', 'guard_name' => $guardName, 'description' => 'Ver bancos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.banks.update', 'guard_name' => $guardName, 'description' => 'Permiso para actualizar bancos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.banks.delete', 'guard_name' => $guardName, 'description' => 'Permiso para eliminar bancos', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.params.support_statuses.index', 'guard_name' => $guardName, 'description' => 'Listar estados de soporte', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.support_statuses.create', 'guard_name' => $guardName, 'description' => 'Crear estados de soporte', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.support_statuses.show', 'guard_name' => $guardName, 'description' => 'Ver estados de soporte', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.support_statuses.update', 'guard_name' => $guardName, 'description' => 'Permiso para actualizar estados de soporte', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.support_statuses.delete', 'guard_name' => $guardName, 'description' => 'Permiso para eliminar estados de soporte', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.params.establishments.index', 'guard_name' => $guardName, 'description' => 'Listar establecimientos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.establishments.create', 'guard_name' => $guardName, 'description' => 'Crear establecimientos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.establishments.show', 'guard_name' => $guardName, 'description' => 'Ver establecimientos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.establishments.update', 'guard_name' => $guardName, 'description' => 'Permiso para actualizar establecimientos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.establishments.delete', 'guard_name' => $guardName, 'description' => 'Permiso para eliminar establecimientos', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.params.pqrs_types.index', 'guard_name' => $guardName, 'description' => 'Listar tipos de PQRs', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.pqrs_types.create', 'guard_name' => $guardName, 'description' => 'Crear tipos de PQRs', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.pqrs_types.show', 'guard_name' => $guardName, 'description' => 'Ver tipos de PQRs', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.pqrs_types.update', 'guard_name' => $guardName, 'description' => 'Permiso para actualizar tipos de PQRs', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.pqrs_types.delete', 'guard_name' => $guardName, 'description' => 'Permiso para eliminar tipos de PQRs', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.params.priority_types.index', 'guard_name' => $guardName, 'description' => 'Listar tipos de prioridad', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.priority_types.create', 'guard_name' => $guardName, 'description' => 'Crear tipos de prioridad', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.priority_types.show', 'guard_name' => $guardName, 'description' => 'Ver tipos de prioridad', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.priority_types.update', 'guard_name' => $guardName, 'description' => 'Permiso para actualizar tipos de prioridad', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.priority_types.delete', 'guard_name' => $guardName, 'description' => 'Permiso para eliminar tipos de prioridad', 'created_at' => now(), 'updated_at' => now()],

            // Gestión de Perfiles
            ['name' => 'admin.profiles.roles.index', 'guard_name' => $guardName, 'description' => 'Listar roles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.roles.create', 'guard_name' => $guardName, 'description' => 'Crear roles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.roles.show', 'guard_name' => $guardName, 'description' => 'Ver roles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.roles.update', 'guard_name' => $guardName, 'description' => 'Permiso para actualizar roles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.roles.delete', 'guard_name' => $guardName, 'description' => 'Permiso para eliminar roles', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.roles.assign_permissions', 'guard_name' => $guardName, 'description' => 'Permiso para asignar permisos a roles', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.profiles.permissions.index', 'guard_name' => $guardName, 'description' => 'Listar permisos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.permissions.create', 'guard_name' => $guardName, 'description' => 'Crear permisos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.permissions.show', 'guard_name' => $guardName, 'description' => 'Ver permisos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.permissions.update', 'guard_name' => $guardName, 'description' => 'Permiso para actualizar permisos', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.permissions.delete', 'guard_name' => $guardName, 'description' => 'Permiso para eliminar permisos', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.profiles.provider.index', 'guard_name' => $guardName, 'description' => 'Listar proveedores', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.provider.create', 'guard_name' => $guardName, 'description' => 'Crear proveedor', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.provider.show', 'guard_name' => $guardName, 'description' => 'Ver proveedor', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.provider.update', 'guard_name' => $guardName, 'description' => 'Permiso para actualizar proveedor', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.provider.delete', 'guard_name' => $guardName, 'description' => 'Permiso para eliminar proveedor', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.profiles.users.index', 'guard_name' => $guardName, 'description' => 'Listar usuarios', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.users.create', 'guard_name' => $guardName, 'description' => 'Crear usuario', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.users.show', 'guard_name' => $guardName, 'description' => 'Ver usuario', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.users.update', 'guard_name' => $guardName, 'description' => 'Permiso para actualizar usuario', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.users.delete', 'guard_name' => $guardName, 'description' => 'Permiso para eliminar usuario', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.users.assign_roles_permissions', 'guard_name' => $guardName, 'description' => 'Permiso para asignar roles y permisos a usuarios', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.users.update_status', 'guard_name' => $guardName, 'description' => 'Permiso para actualizar estado de usuario', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.profiles.administrators.show', 'guard_name' => $guardName, 'description' => 'Permiso para ver detalles de administrador', 'created_at' => now(), 'updated_at' => now()],

            // Módulos de Administrador
            ['name' => 'admin.profiles.show', 'guard_name' => $guardName, 'description' => 'Ver módulo de perfiles de administrador', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.params.show', 'guard_name' => $guardName, 'description' => 'Ver módulo de parametrización de administrador', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.provider_validate.show', 'guard_name' => $guardName, 'description' => 'Ver módulo de validación de proveedor de administrador', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.marketing.show', 'guard_name' => $guardName, 'description' => 'Ver módulo de marketing de administrador', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.dashboard.show', 'guard_name' => $guardName, 'description' => 'Ver módulo de dashboard de administrador', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.support.show', 'guard_name' => $guardName, 'description' => 'Ver módulo de soporte de administrador', 'created_at' => now(), 'updated_at' => now()],

            // Módulos PQRS
            ['name' => 'admin.my_pqrs.show', 'guard_name' => $guardName, 'description' => 'Ver módulo Mis PQRS de administrador', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.manage_pqrs.show', 'guard_name' => $guardName, 'description' => 'Ver módulo gestión PQRS de administrador', 'created_at' => now(), 'updated_at' => now()],

            // Módulos de Proveedor
            ['name' => 'provider.basic_data.show', 'guard_name' => $guardName, 'description' => 'Ver módulo de datos básicos de proveedor', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.branches.show', 'guard_name' => $guardName, 'description' => 'Ver módulo de sucursales de proveedor', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.products.show', 'guard_name' => $guardName, 'description' => 'Ver módulo de productos de proveedor', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.my_account.show', 'guard_name' => $guardName, 'description' => 'Ver módulo de mi cuenta de proveedor', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.my_wallet.show', 'guard_name' => $guardName, 'description' => 'Ver módulo de mi billetera de proveedor', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.dashboard.show', 'guard_name' => $guardName, 'description' => 'Ver módulo de dashboard de proveedor', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.review.show', 'guard_name' => $guardName, 'description' => 'Ver módulo de reseñas de proveedor', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.support.show', 'guard_name' => $guardName, 'description' => 'Ver módulo de soporte de proveedor', 'created_at' => now(), 'updated_at' => now()],

            // Documentos Legales
            ['name' => 'admin.legal_documents.index', 'guard_name' => $guardName, 'description' => 'Listar documentos legales', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin.legal_documents.create', 'guard_name' => $guardName, 'description' => 'Crear documentos legales', 'created_at' => now(), 'updated_at' => now()],

            // Módulo de Sucursales
            ['name' => 'provider.branches.index', 'guard_name' => $guardName, 'description' => 'Listar sucursales', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.branches.create', 'guard_name' => $guardName, 'description' => 'Crear sucursales', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.branches.show', 'guard_name' => $guardName, 'description' => 'Ver sucursales', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.branches.update', 'guard_name' => $guardName, 'description' => 'Actualizar sucursales', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.branches.delete', 'guard_name' => $guardName, 'description' => 'Eliminar sucursales', 'created_at' => now(), 'updated_at' => now()],

            // Módulos de Comercios
            ['name' => 'provider.commerce_payout_methods.index', 'guard_name' => $guardName, 'description' => 'Listar métodos de pago de comercio de proveedor', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.commerces.mine', 'guard_name' => $guardName, 'description' => 'Mostrar mis comercios', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.commerces.index', 'guard_name' => $guardName, 'description' => 'Listar comercios', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.commerces.create', 'guard_name' => $guardName, 'description' => 'Crear comercios', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.commerces.show', 'guard_name' => $guardName, 'description' => 'Ver comercios', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.commerces.update', 'guard_name' => $guardName, 'description' => 'Actualizar comercios', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.commerces.delete', 'guard_name' => $guardName, 'description' => 'Eliminar comercios', 'created_at' => now(), 'updated_at' => now()],

            // Representantes Legales
            ['name' => 'provider.legal_representatives.index', 'guard_name' => $guardName, 'description' => 'Listar representantes legales', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.legal_representatives.create', 'guard_name' => $guardName, 'description' => 'Crear representante legal', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.legal_representatives.show', 'guard_name' => $guardName, 'description' => 'Ver representante legal', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.legal_representatives.update', 'guard_name' => $guardName, 'description' => 'Actualizar representante legal', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.legal_representatives.delete', 'guard_name' => $guardName, 'description' => 'Eliminar representante legal', 'created_at' => now(), 'updated_at' => now()],

            // Tipos de Establecimiento
            ['name' => 'provider.establishment_types.index', 'guard_name' => $guardName, 'description' => 'Listar tipos de establecimiento', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.establishment_types.create', 'guard_name' => $guardName, 'description' => 'Crear tipo de establecimiento', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.establishment_types.show', 'guard_name' => $guardName, 'description' => 'Ver tipo de establecimiento', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.establishment_types.update', 'guard_name' => $guardName, 'description' => 'Actualizar tipo de establecimiento', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.establishment_types.delete', 'guard_name' => $guardName, 'description' => 'Eliminar tipo de establecimiento', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'admin.providers.upload_documents', 'guard_name' => $guardName, 'description' => 'Ver módulo de carga de documentos de proveedor en admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.photos.upload', 'guard_name' => $guardName, 'description' => 'Actualizar fotos por proveedor', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'provider.product_categories.index', 'guard_name' => $guardName, 'description' => 'Listar categorías de producto', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.product_categories.create', 'guard_name' => $guardName, 'description' => 'Crear categoría de producto', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.product_categories.show', 'guard_name' => $guardName, 'description' => 'Ver categoría de producto', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.product_categories.update', 'guard_name' => $guardName, 'description' => 'Actualizar categoría de producto', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.product_categories.delete', 'guard_name' => $guardName, 'description' => 'Eliminar categoría de producto', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'provider.products.index', 'guard_name' => $guardName, 'description' => 'Listar productos por comercio', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.products.create', 'guard_name' => $guardName, 'description' => 'Crear productos por comercio', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.products.show', 'guard_name' => $guardName, 'description' => 'Ver producto por comercio', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.products.update', 'guard_name' => $guardName, 'description' => 'Actualizar productos por comercio', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.products.delete', 'guard_name' => $guardName, 'description' => 'Eliminar producto por comercio', 'created_at' => now(), 'updated_at' => now()],

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
            ['name' => 'superadmin', 'guard_name' => $guardName, 'description' => 'Rol Super Administrador', 'status' => Constant::STATUS_ACTIVE],
            ['name' => 'admin', 'guard_name' => $guardName, 'description' => 'Rol Administrador', 'status' => Constant::STATUS_ACTIVE],
            ['name' => 'provider', 'guard_name' => $guardName, 'description' => 'Rol Proveedor', 'status' => Constant::STATUS_ACTIVE],
            ['name' => 'user', 'guard_name' => $guardName, 'description' => 'Rol Usuario', 'status' => Constant::STATUS_ACTIVE],
            ['name' => 'guest', 'guard_name' => $guardName, 'description' => 'Rol Invitado', 'status' => Constant::STATUS_INACTIVE],
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
