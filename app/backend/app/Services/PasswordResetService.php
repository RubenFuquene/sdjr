<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;

/**
 * Class PasswordResetService
 *
 * Handles the logic for password reset requests.
 */
class PasswordResetService
{
    /**
     * Reset the user's password using token.
     *
     * @throws Exception
     */
    public function resetPassword(array $data): void
    {
        try {
            $status = Password::broker()->reset(
                [
                    'email' => $data['email'],
                    'password' => $data['password'],
                    'token' => $data['token'],
                ],
                function () use ($data) {
                    $user = User::where('email', $data['email'])->first();
                    if (! $user) {
                        throw new Exception('Usuario no encontrado para restablecer la contraseña.');
                    }
                    $user->password = bcrypt($data['password']);
                    $user->save();
                }
            );

            if ($status !== Password::PASSWORD_RESET) {
                Log::error('Password reset failed', [
                    'email' => $data['email'],
                    'status' => $status,
                    'data' => $data,
                ]);
                throw new Exception('No se pudo restablecer la contraseña.');
            }
        } catch (Exception $e) {
            throw $e;
        }
    }

    /**
     * Send password reset link to user email.
     *
     * @throws Exception
     */
    public function sendResetLink(string $email): void
    {
        try {
            $user = User::where('email', $email)->firstOrFail();
            $token = Password::broker()->createToken($user);

            try {
                $user->notify(new ResetPasswordNotification($token, $user->email));
            } catch (\Throwable $e) {
                Log::warning('Reset password notification dispatch failed', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'message' => $e->getMessage(),
                ]);
            }
        } catch (Exception $e) {
            Log::error('Password reset error', [
                'email' => $email,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Create a password reset token for a user (without sending email).
     *
     * This method is used when creating new users without password (e.g., branch leaders).
     * The token will be sent via a separate notification.
     *
     * @param  string  $email  User's email address
     * @return string The generated password reset token
     *
     * @throws Exception
     */
    public function createTokenForUser(string $email): string
    {
        try {
            $user = User::where('email', $email)->firstOrFail();
            $token = Password::broker()->createToken($user);

            Log::info('Password reset token created', [
                'user_id' => $user->id,
                'email' => $email,
            ]);

            return $token;
        } catch (Exception $e) {
            Log::error('Error creating password reset token', [
                'email' => $email,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
