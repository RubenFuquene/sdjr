<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\Commerce;
use App\Services\CommerceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommerceServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test: myCommerce retorna el comercio del usuario
     */
    public function test_my_commerce_returns_user_commerce()
    {
        $user = \App\Models\User::factory()->create();
        $commerce = Commerce::factory()->create(['owner_user_id' => $user->id]);
        $service = new CommerceService;
        $result = $service->myCommerce($user->id);
        $this->assertNotNull($result);
        $this->assertEquals($commerce->id, $result->id);
    }

    /**
     * Test: myCommerce retorna null si el usuario no tiene comercio
     */
    public function test_my_commerce_returns_null_if_no_commerce()
    {
        $user = \App\Models\User::factory()->create();
        $service = new CommerceService;
        $result = $service->myCommerce($user->id);
        $this->assertNull($result);
    }
}
