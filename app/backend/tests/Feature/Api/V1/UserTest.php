<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Models\User;
use App\Notifications\AdminWelcomeNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Permission::firstOrCreate(['name' => 'admin.profiles.users.index', 'guard_name' => 'sanctum']);
        Permission::firstOrCreate(['name' => 'admin.profiles.users.create', 'guard_name' => 'sanctum']);
        Permission::firstOrCreate(['name' => 'admin.profiles.users.show', 'guard_name' => 'sanctum']);
        Permission::firstOrCreate(['name' => 'admin.profiles.users.update', 'guard_name' => 'sanctum']);
        Permission::firstOrCreate(['name' => 'admin.profiles.users.delete', 'guard_name' => 'sanctum']);

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);
        Role::firstOrCreate(['name' => 'superadmin', 'guard_name' => 'sanctum']);
    }

    /**
     * Prueba que un usuario autenticado y con permiso puede listar usuarios.
     */
    public function test_authenticated_user_can_list_users(): void
    {

        $user = User::factory()->create();
        $user->givePermissionTo('admin.profiles.users.index');
        User::factory()->count(3)->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/v1/users');
        $response->assertOk();
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id', 'name', 'last_name', 'email', 'phone', 'roles', 'status', 'created_at', 'updated_at',
                ],
            ],
        ]);
    }

    /**
     * Prueba que un usuario no autenticado no puede listar usuarios.
     */
    public function test_unauthenticated_user_cannot_list_users(): void
    {
        $response = $this->getJson('/api/v1/users');
        $response->assertUnauthorized();
    }

    /**
     * Prueba que un usuario autenticado y con permiso puede crear un usuario.
     */
    public function test_authenticated_user_can_create_user(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('admin.profiles.users.create');
        Sanctum::actingAs($admin);

        $data = [
            'name' => 'New',
            'last_name' => 'User',
            'email' => 'new@example.com',
            'phone' => '3001234567',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => ['admin'],
        ];

        $response = $this->postJson('/api/v1/users', $data);
        $response->assertCreated();
        $response->assertJsonFragment(['name' => 'New', 'last_name' => 'User', 'phone' => '3001234567']);
    }

    /**
     * Prueba que un usuario autenticado y con permiso puede ver el detalle de un usuario.
     */
    public function test_authenticated_user_can_show_single_user(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('admin.profiles.users.show');
        $user = User::factory()->create();
        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/v1/users/'.$user->id);
        $response->assertOk();
        $response->assertJsonFragment(['id' => $user->id]);
    }

    /**
     * Prueba que un usuario autenticado y con permiso puede actualizar un usuario.
     */
    public function test_authenticated_user_can_update_user(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('admin.profiles.users.update');
        $user = User::factory()->create();
        Sanctum::actingAs($admin);

        $data = ['name' => 'Updated Name', 'last_name' => 'Updated LastName', 'roles' => ['admin', 'superadmin']];
        $response = $this->putJson('/api/v1/users/'.$user->id, $data);
        $response->assertOk();
        $response->assertJsonFragment(['name' => 'Updated Name']);
    }

    /**
     * Prueba que un usuario autenticado y con permiso puede eliminar (soft delete) un usuario.
     */
    public function test_authenticated_user_can_delete_user(): void
    {
        $admin = User::factory()->create();
        $admin->givePermissionTo('admin.profiles.users.delete');
        $user = User::factory()->create();
        Sanctum::actingAs($admin);

        $response = $this->deleteJson('/api/v1/users/'.$user->id);
        $response->assertNoContent();
        $this->assertSoftDeleted('users', ['id' => $user->id]);
    }

    public function test_unauthenticated_user_cannot_create_user(): void
    {
        $unauthenticated_user = User::factory()->create();
        Sanctum::actingAs($unauthenticated_user);

        $data = [
            'name' => 'New',
            'last_name' => 'User',
            'email' => 'new@example.com',
            'phone' => '3001234567',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => ['admin'],
        ];

        $response = $this->postJson('/api/v1/users', $data);
        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_update_user(): void
    {
        $unauthenticated_user = User::factory()->create();
        Sanctum::actingAs($unauthenticated_user);

        $user = User::factory()->create();

        $data = ['name' => 'Updated Name', 'last_name' => 'Updated LastName', 'roles' => ['admin', 'superadmin']];
        $response = $this->putJson('/api/v1/users/'.$user->id, $data);
        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_show_user(): void
    {
        $unauthenticated_user = User::factory()->create();
        Sanctum::actingAs($unauthenticated_user);
        $user = User::factory()->create();
        $response = $this->getJson('/api/v1/users/'.$user->id);
        $response->assertForbidden();
    }

    public function test_unauthenticated_user_cannot_delete_user(): void
    {
        $unauthenticated_user = User::factory()->create();
        Sanctum::actingAs($unauthenticated_user);
        $user = User::factory()->create();
        $response = $this->deleteJson('/api/v1/users/'.$user->id);
        $response->assertForbidden();
    }

    public function test_creating_admin_without_password_sends_welcome_invitation(): void
    {
        Notification::fake();

        $admin = User::factory()->create();
        $admin->givePermissionTo('admin.profiles.users.create');
        Sanctum::actingAs($admin);

        $data = [
            'name' => 'Nuevo',
            'last_name' => 'Admin',
            'email' => 'nuevo.admin@example.com',
            'phone' => '3001234567',
            'roles' => ['admin'],
        ];

        $response = $this->postJson('/api/v1/users', $data);

        $response->assertCreated();
        $response->assertJsonFragment(['name' => 'Nuevo', 'last_name' => 'Admin']);

        $newUser = User::where('email', 'nuevo.admin@example.com')->firstOrFail();
        Notification::assertSentTo($newUser, AdminWelcomeNotification::class);
    }

    public function test_admin_welcome_notification_contains_setup_link(): void
    {
        $user = User::factory()->create(['email' => 'inv@example.com', 'name' => 'Invitado']);
        $user->assignRole('admin');

        $mail = (new AdminWelcomeNotification($user, 'setup-token-123'))->toMail($user);

        $this->assertStringContainsString('/admin/reset-password', (string) $mail->actionUrl);
        $this->assertStringContainsString('token=setup-token-123', (string) $mail->actionUrl);
        $this->assertStringContainsString('email=inv%40example.com', (string) $mail->actionUrl);
    }
}
