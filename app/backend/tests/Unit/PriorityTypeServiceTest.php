<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\PriorityType;
use App\Services\PriorityTypeService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PriorityTypeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_priority_type(): void
    {
        $service = new PriorityTypeService;
        $data = [
            'name' => 'Alta',
            'code' => 'HIGH',
            'status' => '1',
        ];
        $priorityType = $service->create($data);
        $this->assertInstanceOf(PriorityType::class, $priorityType);
        $this->assertEquals('Alta', $priorityType->name);
    }

    public function test_update_priority_type(): void
    {
        $service = new PriorityTypeService;
        $priorityType = PriorityType::factory()->create();
        $updated = $service->update($priorityType, ['name' => 'Media']);
        $this->assertEquals('Media', $updated->name);
    }

    public function test_delete_priority_type(): void
    {
        $service = new PriorityTypeService;
        $priorityType = PriorityType::factory()->create();
        $service->delete($priorityType);
        $this->assertNull(PriorityType::find($priorityType->id));
    }

    /**
     * Test getPaginated with name filter
     */
    public function test_get_paginated_with_name_filter(): void
    {
        $service = new PriorityTypeService;
        PriorityType::factory()->create(['name' => 'Alta']);
        PriorityType::factory()->create(['name' => 'Baja']);
        PriorityType::factory()->create(['name' => 'Media']);

        $result = $service->getPaginated(['name' => 'alt'], 10);
        $this->assertCount(1, $result->items());
        $this->assertEquals('Alta', $result->items()[0]->name);
    }
}
