<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Models\User;
use App\Services\AuthService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class AuthServiceTest extends TestCase
{
    use RefreshDatabase;

    private AuthService $authService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authService = new AuthService;
    }

    /**
     * Test: Successful login with valid credentials
     */
    public function test_login_succeeds_with_valid_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        $result = $this->authService->login([
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $this->assertArrayHasKey('token', $result);
        $this->assertArrayHasKey('user', $result);
        $this->assertEquals($user->id, $result['user']->id);
        $this->assertNotEmpty($result['token']);
    }

    /**
     * Test: Login fails with wrong password
     */
    public function test_login_fails_with_wrong_password()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('correct_password'),
        ]);

        $this->expectException(ValidationException::class);

        try {
            $this->authService->login([
                'email' => 'test@example.com',
                'password' => 'wrong_password',
            ]);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors());
            $this->assertEquals('Invalid credentials provided.', $e->errors()['email'][0]);

            throw $e;
        }
    }

    /**
     * Test: Login fails with non-existent user
     */
    public function test_login_fails_with_nonexistent_user()
    {
        $this->expectException(ValidationException::class);

        try {
            $this->authService->login([
                'email' => 'nonexistent@example.com',
                'password' => 'any_password',
            ]);
        } catch (ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors());
            $this->assertEquals('Invalid credentials provided.', $e->errors()['email'][0]);

            throw $e;
        }
    }
}
