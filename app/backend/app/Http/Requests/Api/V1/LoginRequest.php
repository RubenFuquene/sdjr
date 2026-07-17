<?php

namespace App\Http\Requests\Api\V1;

use App\Enums\LoginScope;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *     schema="LoginRequest",
 *     title="Login Request",
 *     description="Login request body data",
 *     required={"email", "password", "scope"},
 *
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         example="user@example.com",
 *         description="User's email address"
 *     ),
 *     @OA\Property(
 *         property="password",
 *         type="string",
 *         format="password",
 *         example="password",
 *         description="User's password"
 *     ),
 *     @OA\Property(
 *         property="scope",
 *         type="string",
 *         enum={"admin", "provider", "customer"},
 *         example="admin",
 *         description="Módulo desde el que se inicia sesión; el rol del usuario debe pertenecer a este ámbito"
 *     )
 * )
 */
class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
            // Ámbito/módulo desde el que se inicia sesión (admin|provider|customer).
            // Obligatorio: el login rechaza credenciales de un módulo ajeno (SCRUM-325).
            'scope' => ['required', Rule::enum(LoginScope::class)],
        ];
    }
}
