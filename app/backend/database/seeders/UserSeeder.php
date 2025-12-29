<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Class UserSeeder
 *
 * Seeder responsible for populating the users table.
 * Creates a default admin user and a set of random users for testing.
 */
class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create a specific admin user for testing purposes
        User::factory()->create([
            'name' => 'Admin',
            'last_name' => 'User',
            'email' => 'admin@example.com',
            'phone' => '3001234567',
            'password' => Hash::make('password'),
            'email_verified_at' => now(),
        ]);

        // Create 10 random users
        User::factory(10)->create();
    }
}
