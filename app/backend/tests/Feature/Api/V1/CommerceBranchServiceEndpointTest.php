<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Models\Commerce;
use App\Models\CommerceBranch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class CommerceBranchServiceEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Permission::create(['name' => 'provider.commerces.view', 'guard_name' => 'sanctum']);
    }

    public function test_get_branches_by_commerce_id_returns_paginated(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerces.view');
        $commerce = Commerce::factory()->create();
        CommerceBranch::factory()->count(5)->create(['commerce_id' => $commerce->id]);
        $this->actingAs($user, 'sanctum');
        $response = $this->getJson("/api/v1/commerces/{$commerce->id}/branches?per_page=3");
        $response->assertOk();
        $this->assertEquals(3, count($response->json('data')));
        $this->assertEquals($commerce->id, $response->json('data')[0]['commerce_id']);
    }

    public function test_get_branches_by_commerce_id_throws_for_nonexistent(): void
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.commerces.view');
        $this->actingAs($user, 'sanctum');
        $response = $this->getJson('/api/v1/commerces/99999/branches');
        $response->assertNotFound();
    }
}
