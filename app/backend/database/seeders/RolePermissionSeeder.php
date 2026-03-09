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
            ['name' => 'provider.products.update', 'guard_name' => $guardName, 'description' => 'Actualizar productos por comercio', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider.products.delete', 'guard_name' => $guardName, 'description' => 'Eliminar producto por comercio', 'created_at' => now(), 'updated_at' => now()],

        ];

        // Crear permisos si no existen
        Permission::insert($permissions);

        // Crear roles si no existen
        $roles = [
            ['name' => 'superadmin', 'guard_name' => $guardName, 'description' => 'Rol Super Administrador', 'status' => Constant::STATUS_ACTIVE],
            ['name' => 'admin', 'guard_name' => $guardName, 'description' => 'Rol Administrador', 'status' => Constant::STATUS_ACTIVE],
            ['name' => 'provider', 'guard_name' => $guardName, 'description' => 'Rol Proveedor', 'status' => Constant::STATUS_ACTIVE],
            ['name' => 'user', 'guard_name' => $guardName, 'description' => 'Rol Usuario', 'status' => Constant::STATUS_ACTIVE],
            ['name' => 'support', 'guard_name' => $guardName, 'description' => 'Rol Soporte', 'status' => Constant::STATUS_ACTIVE],
            ['name' => 'guest', 'guard_name' => $guardName, 'description' => 'Rol Invitado', 'status' => Constant::STATUS_INACTIVE],
        ];

        Role::insert($roles);

        // Asignar permisos a roles
        // Superadmin tiene todos los permisos
        $role = Role::where('name', 'superadmin')->first();
        $role->givePermissionTo(Permission::all());

        // Admin tiene permisos específicos
        $indexShowPermissions = [
            // Index
            'admin.params.countries.index',
            'admin.params.departments.index',
            'admin.params.cities.index',
            'admin.params.neighborhoods.index',
            'admin.params.categories.index',
            'admin.params.banks.index',
            'admin.params.support_statuses.index',
            'admin.params.establishments.index',
            'admin.params.pqrs_types.index',
            'admin.params.priority_types.index',
            'admin.profiles.roles.index',
            'admin.profiles.permissions.index',
            'admin.profiles.provider.index',
            'admin.profiles.users.index',
            'admin.legal_documents.index',
            'provider.branches.index',
            'provider.commerce_payout_methods.index',
            'provider.commerces.index',
            'provider.legal_representatives.index',
            'provider.establishment_types.index',
            'provider.product_categories.index',
            'provider.products.index',
            // Show
            'admin.params.show',
            'admin.params.countries.show',
            'admin.params.departments.show',
            'admin.params.cities.show',
            'admin.params.neighborhoods.show',
            'admin.params.categories.show',
            'admin.params.banks.show',
            'admin.params.support_statuses.show',
            'admin.params.establishments.show',
            'admin.params.pqrs_types.show',
            'admin.params.priority_types.show',
            'admin.profiles.show',
            'admin.profiles.roles.show',
            'admin.profiles.permissions.show',
            'admin.profiles.provider.show',
            'admin.profiles.users.show',
            'admin.provider_validate.show',
            'admin.marketing.show',
            'admin.dashboard.show',
            'admin.support.show',
            'admin.profiles.administrators.show',
            'provider.basic_data.show',
            'provider.branches.show',
            'provider.products.show',
            'provider.my_account.show',
            'provider.my_wallet.show',
            'provider.dashboard.show',
            'provider.review.show',
            'provider.support.show',
            'provider.commerces.mine',
            'provider.commerces.show',
            'provider.legal_representatives.show',
            'provider.establishment_types.show',
            'provider.product_categories.show',
            'provider.products.show',
            'admin.my_pqrs.show',
            'admin.manage_pqrs.show',
        ];

        $adminPermissions = array_merge($indexShowPermissions, [
            // Create
            'admin.params.countries.create',
            'admin.params.departments.create',
            'admin.params.cities.create',
            'admin.params.neighborhoods.create',
            'admin.params.categories.create',
            'admin.params.banks.create',
            'admin.params.support_statuses.create',
            'admin.params.establishments.create',
            'admin.params.pqrs_types.create',
            'admin.params.priority_types.create',
            'admin.profiles.roles.create',
            'admin.profiles.permissions.create',
            'admin.profiles.provider.create',
            'admin.profiles.users.create',
            'admin.legal_documents.create',
            'provider.branches.create',
            'provider.commerces.create',
            'provider.legal_representatives.create',
            'provider.establishment_types.create',
            'provider.product_categories.create',
            'provider.products.create',

            // Update
            'admin.params.countries.update',
            'admin.params.departments.update',
            'admin.params.cities.update',
            'admin.params.neighborhoods.update',
            'admin.params.categories.update',
            'admin.params.banks.update',
            'admin.params.support_statuses.update',
            'admin.params.establishments.update',
            'admin.params.pqrs_types.update',
            'admin.params.priority_types.update',
            'admin.profiles.roles.update',
            'admin.profiles.permissions.update',
            'admin.profiles.provider.update',
            'admin.profiles.users.update',
            'provider.branches.update',
            'provider.commerces.update',
            'provider.legal_representatives.update',
            'provider.establishment_types.update',
            'provider.product_categories.update',
            'provider.products.update',

            // Delete
            'admin.params.countries.delete',
            'admin.params.departments.delete',
            'admin.params.cities.delete',
            'admin.params.neighborhoods.delete',
            'admin.params.categories.delete',
            'admin.params.banks.delete',
            'admin.params.support_statuses.delete',
            'admin.params.establishments.delete',
            'admin.params.pqrs_types.delete',
            'admin.params.priority_types.delete',
            'admin.profiles.roles.delete',
            'admin.profiles.permissions.delete',
            'admin.profiles.provider.delete',
            'admin.profiles.users.delete',
            'provider.branches.delete',
            'provider.commerces.delete',
            'provider.legal_representatives.delete',
            'provider.establishment_types.delete',
            'provider.product_categories.delete',
            'provider.products.delete',
        ]);

        $adminRole = Role::where('name', 'admin')->first();
        $adminRole->givePermissionTo($adminPermissions);

        // Proveedor tiene permisos específicos
        $providerPermissions = array_merge($indexShowPermissions, [
            // Create
            'provider.branches.create',
            'provider.commerces.create',
            'provider.legal_representatives.create',
            'provider.establishment_types.create',
            'provider.product_categories.create',
            'provider.products.create',

            // Update
            'provider.branches.update',
            'provider.commerces.update',
            'provider.legal_representatives.update',
            'provider.establishment_types.update',
            'provider.product_categories.update',
            'provider.products.update',

            // Delete
            'provider.branches.delete',
            'provider.commerces.delete',
            'provider.legal_representatives.delete',
            'provider.establishment_types.delete',
            'provider.product_categories.delete',
            'provider.products.delete',

            // Permisos adicionales de proveedor
            'admin.providers.upload_documents',
            'provider.photos.upload',
        ]);

        $providerRole = Role::where('name', 'provider')->first();
        $providerRole->givePermissionTo($providerPermissions);

        // Usuario tiene permisos específicos
        $userPermissions = array_merge($indexShowPermissions, [

        ]);

        $userRole = Role::where('name', 'user')->first();
        $userRole->givePermissionTo($userPermissions);

        // Soporte tiene permisos específicos
        $supportPermissions = [
            'admin.my_pqrs.show',
            'admin.manage_pqrs.show',
        ];

        $supportRole = Role::where('name', 'support')->first();
        $supportRole->givePermissionTo($supportPermissions);

        // Invitado no tiene permisos asignados

        // Asignar rol a un usuario específico
        $user = User::first();
        $user->assignRole('superadmin');
    }
}
