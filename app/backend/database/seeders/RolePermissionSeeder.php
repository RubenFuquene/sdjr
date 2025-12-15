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

        // Crear permisos
        Permission::insert([

            // Parametrization
            ['name' => 'countries.index', 'guard_name' => $guardName, 'description' => 'List countries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'countries.create', 'guard_name' => $guardName, 'description' => 'Create countries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'countries.show', 'guard_name' => $guardName, 'description' => 'View countries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'countries.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit countries', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'countries.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete countries', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'departments.index', 'guard_name' => $guardName, 'description' => 'List departments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'departments.create', 'guard_name' => $guardName, 'description' => 'Create departments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'departments.show', 'guard_name' => $guardName, 'description' => 'View departments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'departments.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit departments', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'departments.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete departments', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'cities.index', 'guard_name' => $guardName, 'description' => 'List cities', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'cities.create', 'guard_name' => $guardName, 'description' => 'Create cities', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'cities.show', 'guard_name' => $guardName, 'description' => 'View cities', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'cities.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit cities', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'cities.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete cities', 'created_at' => now(), 'updated_at' => now()],

            ['name' => 'categories.index', 'guard_name' => $guardName, 'description' => 'List categories', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'categories.create', 'guard_name' => $guardName, 'description' => 'Create categories', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'categories.show', 'guard_name' => $guardName, 'description' => 'View categories', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'categories.edit', 'guard_name' => $guardName, 'description' => 'Permission to edit categories', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'categories.delete', 'guard_name' => $guardName, 'description' => 'Permission to delete categories', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Crear roles y asignar permisos
        Role::insert([
            ['name' => 'superadmin', 'guard_name' => $guardName, 'description' => 'Super Administrator role', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'admin', 'guard_name' => $guardName, 'description' => 'Administrator role', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'provider', 'guard_name' => $guardName, 'description' => 'Provider role', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'user', 'guard_name' => $guardName, 'description' => 'User role', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Asignar permisos a roles
        $role = Role::where('name', 'superadmin')->first();        
        $role->givePermissionTo(Permission::all());

        // Asignar rol a un usuario específico
        $user = User::first();
        $user->assignRole('superadmin'); 
    }
}
