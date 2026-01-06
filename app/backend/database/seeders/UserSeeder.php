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
        // Create a specific admin user for testing purposes (updateOrCreate for idempotency)
        User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin',
                'last_name' => 'User',
                'phone' => '3001234567',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Only create random users if they don't exist
        if (User::count() <= 1) {
            User::factory(10)->create();
        }
    }

    protected function getRecordsCount(): int
    {
        return User::count();
    }
}
