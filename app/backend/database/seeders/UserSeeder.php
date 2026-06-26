<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserSeeder
 *
 * Garantiza un usuario superadmin base en todos los entornos (idempotente por email,
 * credenciales tomadas de variables de entorno) y, bajo DEMO_SEEDING, un set de usuarios
 * de prueba.
 *
 * Es un Seeder plano (no ControlledSeeder): debe ejecutarse en cada deploy para asegurar
 * que el superadmin exista; la idempotencia la garantiza firstOrCreate por email.
 */
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Superadmin base: debe existir en todos los entornos para poder operar.
        // Las credenciales se toman de variables de entorno; el valor por defecto es un
        // placeholder que DEBE sobreescribirse vía SEED_ADMIN_* en staging/producción.
        // Se usa firstOrCreate para no sobrescribir la contraseña si ya fue cambiada.
        $superadmin = User::firstOrCreate(
            ['email' => env('SEED_ADMIN_EMAIL', 'admin@napaapp.com')],
            [
                'name' => env('SEED_ADMIN_NAME', 'Administrator'),
                'last_name' => env('SEED_ADMIN_LAST_NAME', 'Ñapa App'),
                'phone' => env('SEED_ADMIN_PHONE', '3000000000'),
                'password' => Hash::make(env('SEED_ADMIN_PASSWORD', 'ChangeMe!Napa2026')),
                'email_verified_at' => now(),
            ],
        );

        $superadmin->assignRole('superadmin');

        if (env('DEMO_SEEDING') == 'true') {
            $admin = User::firstOrCreate(
                ['email' => 'admin@example.com'],
                [
                    'name' => 'Admin',
                    'last_name' => 'User',
                    'phone' => '3000000001',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
            );
            $admin->assignRole('admin');

            $provider = User::firstOrCreate(
                ['email' => 'provider@example.com'],
                [
                    'name' => 'Provider',
                    'last_name' => 'User',
                    'phone' => '3000000002',
                    'password' => Hash::make('password'),
                    'email_verified_at' => now(),
                ],
            );
            $provider->assignRole('provider');

            // Usuarios de prueba adicionales (solo si aún no se generaron).
            if (User::count() < 13) {
                User::factory(10)->create();
            }
        }
    }
}
