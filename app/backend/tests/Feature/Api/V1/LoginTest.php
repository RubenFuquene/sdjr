<?php

declare(strict_types=1);

namespace Tests\Feature\Api\V1;

use App\Constants\Constant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Cobertura de SCRUM-325: el login valida el ámbito (módulo) y el estado del usuario.
 */
class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['superadmin', 'admin', 'support', 'provider', 'branch_leader', 'user'] as $role) {
            Role::create(['name' => $role, 'guard_name' => 'sanctum']);
        }
    }

    /**
     * Crea un usuario activo con la contraseña por defecto del factory ('password') y un rol.
     */
    private function userWithRole(string $role, int $status = Constant::STATUS_ACTIVE): User
    {
        $user = User::factory()->create(['status' => $status]);
        $user->assignRole($role);

        return $user;
    }

    private function attemptLogin(User $user, string $scope): \Illuminate\Testing\TestResponse
    {
        return $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
            'scope' => $scope,
        ]);
    }

    // ---- Flujo feliz por ámbito -------------------------------------------------

    public function test_provider_can_login_with_provider_scope(): void
    {
        $user = $this->userWithRole('provider');

        $this->attemptLogin($user, 'provider')
            ->assertOk()
            ->assertJsonPath('message', 'Login successful')
            ->assertJsonStructure(['token', 'data' => ['id', 'email', 'roles']]);
    }

    public function test_branch_leader_can_login_with_provider_scope(): void
    {
        $user = $this->userWithRole('branch_leader');

        $this->attemptLogin($user, 'provider')->assertOk();
    }

    public function test_admin_can_login_with_admin_scope(): void
    {
        $user = $this->userWithRole('admin');

        $this->attemptLogin($user, 'admin')->assertOk();
    }

    public function test_customer_can_login_with_customer_scope(): void
    {
        $user = $this->userWithRole('user');

        $this->attemptLogin($user, 'customer')->assertOk();
    }

    // ---- Rechazo por ámbito ajeno (estricto) ------------------------------------

    public function test_customer_cannot_login_on_admin_scope(): void
    {
        $user = $this->userWithRole('user');

        $response = $this->attemptLogin($user, 'admin');

        $response->assertStatus(422)
            ->assertJsonMissingPath('token')
            ->assertJsonValidationErrors('email');
    }

    public function test_customer_cannot_login_on_provider_scope(): void
    {
        $user = $this->userWithRole('user');

        $this->attemptLogin($user, 'provider')
            ->assertStatus(422)
            ->assertJsonMissingPath('token');
    }

    public function test_admin_cannot_login_on_provider_scope_strict(): void
    {
        $user = $this->userWithRole('admin');

        $this->attemptLogin($user, 'provider')
            ->assertStatus(422)
            ->assertJsonMissingPath('token');
    }

    public function test_superadmin_is_strict_and_cannot_login_on_customer_scope(): void
    {
        $user = $this->userWithRole('superadmin');

        // Decisión del proyecto: mapa estricto, sin god-mode multi-módulo.
        $this->attemptLogin($user, 'customer')
            ->assertStatus(422)
            ->assertJsonMissingPath('token');
    }

    // ---- Estado del usuario -----------------------------------------------------

    public function test_inactive_user_cannot_login_even_with_correct_scope(): void
    {
        $user = $this->userWithRole('provider', Constant::STATUS_INACTIVE);

        $this->attemptLogin($user, 'provider')
            ->assertStatus(422)
            ->assertJsonMissingPath('token');
    }

    // ---- Validación del campo scope ---------------------------------------------

    public function test_scope_is_required(): void
    {
        $user = $this->userWithRole('admin');

        $this->postJson('/api/v1/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertStatus(422)->assertJsonValidationErrors('scope');
    }

    public function test_invalid_scope_is_rejected(): void
    {
        $user = $this->userWithRole('admin');

        $this->attemptLogin($user, 'root')
            ->assertStatus(422)
            ->assertJsonValidationErrors('scope');
    }

    // ---- Anti-enumeración: mismo mensaje en las 3 causas ------------------------

    public function test_failure_message_is_identical_across_causes(): void
    {
        $wrongPasswordUser = $this->userWithRole('admin');
        $foreignScopeUser = $this->userWithRole('user');
        $inactiveUser = $this->userWithRole('admin', Constant::STATUS_INACTIVE);

        $badCredential = $this->postJson('/api/v1/login', [
            'email' => $wrongPasswordUser->email,
            'password' => 'incorrect-password',
            'scope' => 'admin',
        ])->json('errors.email');

        $foreignScope = $this->attemptLogin($foreignScopeUser, 'admin')->json('errors.email');
        $inactive = $this->attemptLogin($inactiveUser, 'admin')->json('errors.email');

        $this->assertSame($badCredential, $foreignScope);
        $this->assertSame($badCredential, $inactive);
    }
}
