<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserSeeder
 *
 * Seeder responsible for populating the users table.
 * Creates a default admin user and a set of random users for testing.
 */
class UserSeeder extends ControlledSeeder
{
    protected string $version = '1.0';

    protected bool $idempotent = true; // Can be safely re-run

    /**
     * Run the database seeds.
     */
    protected function runSeeder(): void
    {
        User::insert([
            [
                'name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@example.com',
                'phone' => '3000000000',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Provider',
                'last_name' => 'User',
                'email' => 'provider@example.com',
                'phone' => '3000000001',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        ]);

        // Always create 10 additional test users
        User::factory(10)->create();
    }
}
