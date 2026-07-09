<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\CommerceComment;
use App\Models\PriorityType;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Feature tests for CommerceComment endpoints (incluye SCRUM-297: comentarios RJ,
 * ownership y visibilidad por rol).
 */
class CommerceCommentFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['index', 'create', 'show', 'update', 'delete'] as $action) {
            Permission::firstOrCreate(['name' => "provider.comments.$action", 'guard_name' => 'sanctum']);
        }
        Permission::firstOrCreate(['name' => 'provider.commerces.update', 'guard_name' => 'sanctum']);

        // El rol admin omite la verificación de ownership y ve todos los tipos de comentario.
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);
        $adminRole->givePermissionTo(Permission::all());
    }

    /**
     * Authenticate as an admin (bypass ownership, full visibility).
     */
    private function actingAsAdmin(): User
    {
        $user = User::factory()->create();
        $user->assignRole('admin');
        $this->actingAs($user, 'sanctum');

        return $user;
    }

    /**
     * Authenticate as the owner of the given commerce with the provided permissions.
     */
    private function actingAsCommerceOwner(Commerce $commerce, array $permissions): User
    {
        $user = User::factory()->create();
        $user->givePermissionTo($permissions);
        $commerce->update(['owner_user_id' => $user->id]);
        $this->actingAs($user, 'sanctum');

        return $user;
    }

    // ----- CRUD camino feliz (actor admin) -----

    public function test_index_comments_success(): void
    {
        $commerce = Commerce::factory()->create();
        CommerceComment::factory()->count(3)->create(['commerce_id' => $commerce->id]);
        $this->actingAsAdmin();

        $response = $this->getJson("/api/v1/commerces/{$commerce->id}/comments");
        $response->assertStatus(200)->assertJsonStructure(['data'])->assertJsonCount(3, 'data');
    }

    public function test_index_comments_exposes_creator_name(): void
    {
        $commerce = Commerce::factory()->create();
        $author = User::factory()->create(['name' => 'Ana Gómez']);
        CommerceComment::factory()->create([
            'commerce_id' => $commerce->id,
            'created_by' => $author->id,
        ]);
        $this->actingAsAdmin();

        $response = $this->getJson("/api/v1/commerces/{$commerce->id}/comments");
        $response->assertStatus(200)
            ->assertJsonPath('data.0.created_by_user.id', $author->id)
            ->assertJsonPath('data.0.created_by_user.name', 'Ana Gómez');
    }

    public function test_store_comment_success(): void
    {
        $commerce = Commerce::factory()->create();
        $priorityType = PriorityType::factory()->create();
        $this->actingAsAdmin();

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
        $this->actingAsAdmin();

        $response = $this->getJson("/api/v1/commerces/{$commerce->id}/comments/{$comment->id}");
        $response->assertStatus(200)->assertJsonStructure(['data']);
    }

    public function test_update_comment_success(): void
    {
        $commerce = Commerce::factory()->create();
        $comment = CommerceComment::factory()->create(['commerce_id' => $commerce->id]);
        $this->actingAsAdmin();

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
        $this->actingAsAdmin();

        $response = $this->deleteJson("/api/v1/commerces/{$commerce->id}/comments/{$comment->id}");
        $response->assertStatus(204);
    }

    // ----- Matriz SCRUM-297 -----

    /** 1. Creación RJ exitosa (201) y persistida. */
    public function test_admin_creates_rejection_comment_success(): void
    {
        $commerce = Commerce::factory()->create();
        $priorityType = PriorityType::factory()->create();
        $this->actingAsAdmin();

        $payload = [
            'comment' => 'Documentación incompleta, falta RUT.',
            'priority_type_id' => $priorityType->id,
            'comment_type' => Constant::COMMENT_TYPE_REJECTION,
        ];

        $response = $this->postJson("/api/v1/commerces/{$commerce->id}/comments", $payload);

        $response->assertStatus(201);
        $this->assertDatabaseHas('commerce_comments', [
            'commerce_id' => $commerce->id,
            'comment_type' => Constant::COMMENT_TYPE_REJECTION,
        ]);
    }

    /** 2. comment_type fuera del catálogo → 422. */
    public function test_store_comment_invalid_type_returns_422(): void
    {
        $commerce = Commerce::factory()->create();
        $priorityType = PriorityType::factory()->create();
        $this->actingAsAdmin();

        $payload = [
            'comment' => 'Comentario',
            'priority_type_id' => $priorityType->id,
            'comment_type' => 'ZZ',
        ];

        $response = $this->postJson("/api/v1/commerces/{$commerce->id}/comments", $payload);
        $response->assertStatus(422)->assertJsonValidationErrors('comment_type');
    }

    /** 3. Creación sin permiso → 403. */
    public function test_store_comment_without_permission_returns_403(): void
    {
        $commerce = Commerce::factory()->create();
        $user = User::factory()->create(); // sin permisos ni rol
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson("/api/v1/commerces/{$commerce->id}/comments", [
            'comment' => 'Comentario de prueba',
        ]);
        $response->assertStatus(403);
    }

    /** 4. Lectura por proveedor dueño → 200 y solo comentarios RJ visibles. */
    public function test_provider_owner_sees_only_rejection_comments(): void
    {
        $commerce = Commerce::factory()->create();
        CommerceComment::factory()->create(['commerce_id' => $commerce->id, 'comment_type' => Constant::COMMENT_TYPE_VALIDATION]);
        CommerceComment::factory()->create(['commerce_id' => $commerce->id, 'comment_type' => Constant::COMMENT_TYPE_INFO]);
        CommerceComment::factory()->create(['commerce_id' => $commerce->id, 'comment_type' => Constant::COMMENT_TYPE_REJECTION]);

        $this->actingAsCommerceOwner($commerce, ['provider.comments.index']);

        $response = $this->getJson("/api/v1/commerces/{$commerce->id}/comments");

        $response->assertStatus(200)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.comment_type.code', Constant::COMMENT_TYPE_REJECTION);
    }

    /** 5. Lectura por tercero (con permiso pero sin ser dueño ni admin) → 403. Cierra A01. */
    public function test_non_owner_cannot_list_comments_returns_403(): void
    {
        $commerce = Commerce::factory()->create();
        $user = User::factory()->create();
        $user->givePermissionTo('provider.comments.index');
        $this->actingAs($user, 'sanctum');

        $response = $this->getJson("/api/v1/commerces/{$commerce->id}/comments");
        $response->assertStatus(403);
    }

    /** 6. Regresión: rechazar un comercio persiste la observación como comentario RJ. */
    public function test_reject_commerce_persists_rejection_comment(): void
    {
        Notification::fake();
        PriorityType::factory()->create(['code' => Constant::COMMENT_PRIORITY_HIGH]);
        $commerce = Commerce::factory()->create(['is_verified' => Constant::COMMERCE_PENDING]);
        $this->actingAsAdmin();

        $payload = [
            'is_verified' => Constant::COMMERCE_REJECTED,
            'message' => 'La cámara de comercio no es legible, por favor reenviar.',
        ];

        $response = $this->patchJson("/api/v1/commerces/{$commerce->id}/verification", $payload);

        $response->assertStatus(200)->assertJsonStructure(['data']);
        $this->assertDatabaseHas('commerce_comments', [
            'commerce_id' => $commerce->id,
            'comment_type' => Constant::COMMENT_TYPE_REJECTION,
            'comment' => 'La cámara de comercio no es legible, por favor reenviar.',
        ]);
    }
}
