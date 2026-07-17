<?php

namespace App\Services;

use App\Constants\Constant;
use App\Enums\LoginScope;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    /**
     * Authenticate a user and generate a token.
     *
     * @throws ValidationException
     */
    public function login(array $credentials): array
    {
        $user = User::where('email', $credentials['email'])->first();

        // Security: Check if user exists, has a password set, and password matches
        // All failures return the same message to prevent user enumeration
        if (! $user || is_null($user->password) || ! Hash::check($credentials['password'], $user->password)) {
            $this->failAuthentication();
        }

        // SCRUM-325 — Defensa en profundidad: el login solo acepta usuarios activos y cuyo rol
        // pertenezca al ámbito (módulo) desde el que se inicia sesión. Se usa el MISMO mensaje
        // genérico que para credenciales inválidas para no revelar la causa (anti-enumeración).
        if ((int) $user->status !== Constant::STATUS_ACTIVE) {
            $this->failAuthentication();
        }

        $scope = LoginScope::from($credentials['scope']);
        if (! $user->hasAnyRole($scope->allowedRoles())) {
            $this->failAuthentication();
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'token' => $token,
            'user' => $user,
        ];
    }

    /**
     * Lanza el error de autenticación genérico.
     *
     * Se usa para TODAS las causas de fallo (credencial inválida, usuario inactivo, rol fuera
     * del ámbito) con el mismo mensaje, para evitar enumeración de usuarios/roles/estado.
     *
     * @throws ValidationException
     */
    private function failAuthentication(): never
    {
        throw ValidationException::withMessages([
            'email' => [__('auth.failed')],
        ]);
    }
}
