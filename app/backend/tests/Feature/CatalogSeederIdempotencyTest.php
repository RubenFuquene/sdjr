<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Constants\Constant;
use Database\Seeders\CatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CatalogSeederIdempotencyTest extends TestCase
{
    use RefreshDatabase;

    private const COUNTED_TABLES = [
        'permissions',
        'roles',
        'priority_types',
        'countries',
        'departments',
        'cities',
        'neighborhoods',
        'establishment_types',
        'banks',
    ];

    public function test_running_catalog_seeder_twice_does_not_duplicate_rows(): void
    {
        $this->seed(CatalogSeeder::class);

        $countsAfterFirstRun = $this->countCatalogTables();

        $this->seed(CatalogSeeder::class);

        $countsAfterSecondRun = $this->countCatalogTables();

        $this->assertSame($countsAfterFirstRun, $countsAfterSecondRun);
    }

    public function test_catalog_seeder_leaves_critical_catalog_present(): void
    {
        $this->seed(CatalogSeeder::class);

        $this->assertDatabaseHas('priority_types', ['code' => Constant::COMMENT_PRIORITY_HIGH]);
        $this->assertDatabaseHas('roles', ['name' => 'superadmin', 'guard_name' => 'sanctum']);
        $this->assertDatabaseHas('countries', ['code' => 'CO']);
        $this->assertDatabaseHas('departments', ['code' => '11', 'name' => 'Bogotá D.C.']);
        $this->assertDatabaseHas('cities', ['code' => '11001', 'name' => 'Bogotá D.C.']);
        $this->assertDatabaseCount('neighborhoods', 1091);
        $this->assertDatabaseHas('establishment_types', ['code' => 'RE']);
        $this->assertDatabaseHas('establishment_types', ['code' => 'PA']);
        $this->assertDatabaseHas('establishment_types', ['code' => 'RT']);

        $superadmin = Role::where('name', 'superadmin')->first();
        $this->assertNotNull($superadmin);
        $this->assertGreaterThan(0, $superadmin->permissions()->count());
    }

    public function test_catalog_seeder_does_not_create_demo_data(): void
    {
        $this->seed(CatalogSeeder::class);

        $this->assertDatabaseCount('commerces', 0);
        $this->assertDatabaseCount('orders', 0);
    }

    /**
     * @return array<string, int>
     */
    private function countCatalogTables(): array
    {
        $counts = [];

        foreach (self::COUNTED_TABLES as $table) {
            $counts[$table] = DB::table($table)->count();
        }

        return $counts;
    }
}
