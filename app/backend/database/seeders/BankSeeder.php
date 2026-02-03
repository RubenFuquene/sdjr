<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Bank;
use Illuminate\Database\Seeder;

class BankSeeder extends Seeder
{
    public function run(): void
    {
        if(env('APP_ENV') == 'prd') {
            
            Bank::insert([
                ['name' => 'Bank of America', 'code' => '001', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Chase Bank', 'code' => '002', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Wells Fargo', 'code' => '003', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'Citibank', 'code' => '004', 'created_at' => now(), 'updated_at' => now()],
                ['name' => 'US Bank', 'code' => '005', 'created_at' => now(), 'updated_at' => now()],
            ]);
                        
        }
            
        if(env('DEMO_SEEDING') == 'true') {
            Bank::factory()->count(10)->create();            
        }

    }
}
