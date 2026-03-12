<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\CommerceComment;
use App\Models\PriorityType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * Feature tests for CommerceComment endpoints
 */
class CommerceCommentFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Permission::create(['name' => 'provider.comments.index', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'provider.comments.create', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'provider.comments.show', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'provider.comments.update', 'guard_name' => 'sanctum']);
        Permission::create(['name' => 'provider.comments.delete', 'guard_name' => 'sanctum']);

    }

    public function test_index_comments_success(): void
    {
        $commerce = Commerce::factory()->create();
        CommerceComment::factory()->count(3)->create(['commerce_id' => $commerce->id]);
        $user = User::factory()->create();
        $user->givePermissionTo('provider.comments.index');
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson("/api/v1/commerces/{$commerce->id}/comments");
        $response->assertStatus(200)->assertJsonStructure(['data']);
    }

    public function test_store_comment_success(): void
    {
        $commerce = Commerce::factory()->create();
        $priorityType = PriorityType::factory()->create();
        $user = User::factory()->create();
        $user->givePermissionTo('provider.comments.create');
        $this->actingAs($user, 'sanctum');

        $payload = [
            'comment' => 'Comentario de prueba',
            'priority_type_id' => $priorityType->id,
            'comment_type' => Constant::COMMENT_TYPE_INFO,
            'color' => 'red',
        ];

        $response = $this->postJson("/api/v1/commerces/{$commerce->id}/comments", $payload);
        $response->assertStatus(201)->assertJsonStructure(['data']);
    }

    public function test_show_comment_success(): void
    {
        $commerce = Commerce::factory()->create();
        $comment = CommerceComment::factory()->create(['commerce_id' => $commerce->id]);
        $user = User::factory()->create();
        $user->givePermissionTo('provider.comments.show');
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson("/api/v1/commerces/{$commerce->id}/comments/{$comment->id}");
        $response->assertStatus(200)->assertJsonStructure(['data']);
    }

    public function test_update_comment_success(): void
    {
        $commerce = Commerce::factory()->create();
        $comment = CommerceComment::factory()->create(['commerce_id' => $commerce->id]);
        $user = User::factory()->create();
        $user->givePermissionTo('provider.comments.update');
        $this->actingAs($user, 'sanctum');

        $payload = [
            'comment' => 'Comentario actualizado',
            'comment_type' => Constant::COMMENT_TYPE_INFO,
        ];

        $response = $this->putJson("/api/v1/commerces/{$commerce->id}/comments/{$comment->id}", $payload);
        $response->assertStatus(200)->assertJsonStructure(['data']);
    }

    public function test_delete_comment_success(): void
    {
        $commerce = Commerce::factory()->create();
        $comment = CommerceComment::factory()->create(['commerce_id' => $commerce->id]);
        $user = User::factory()->create();
        $user->givePermissionTo('provider.comments.delete');
        $this->actingAs($user, 'sanctum');

        $response = $this->deleteJson("/api/v1/commerces/{$commerce->id}/comments/{$comment->id}");
        $response->assertStatus(204);
    }

    public function test_store_comment_unauthorized(): void
    {
        $commerce = Commerce::factory()->create();
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $payload = [
            'comment' => 'Comentario de prueba',
        ];

        $response = $this->postJson("/api/v1/commerces/{$commerce->id}/comments", $payload);
        $response->assertStatus(403);
    }
}
