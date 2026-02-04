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
            $user->notify(new ResetPasswordNotification($token, $user->email));
        } catch (Exception $e) {
            Log::error('Password reset error', [
                'email' => $email,
                'message' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}
