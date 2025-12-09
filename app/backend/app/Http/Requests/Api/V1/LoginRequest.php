<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="LoginRequest",
 *     title="Login Request",
 *     description="Login request body data",
 *     required={"email", "password"},
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
        ];
    }
}
