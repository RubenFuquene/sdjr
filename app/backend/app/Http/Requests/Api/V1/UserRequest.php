<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UserRequest",
 *     required={"name", "last_name", "email", "password"},
 *
 *     @OA\Property(property="name", type="string", maxLength=255, example="Juan", description="User first name"),
 *     @OA\Property(property="last_name", type="string", maxLength=255, example="Pérez", description="User last name"),
 *     @OA\Property(property="email", type="string", format="email", example="juan.perez@example.com", description="User email address"),
 *     @OA\Property(property="phone", type="string", maxLength=20, example="3001234567", description="User phone number"),
 *     @OA\Property(property="password", type="string", minLength=8, example="secret123", description="User password (required on create)"),
 *     @OA\Property(property="password_confirmation", type="string", minLength=8, example="secret123", description="Password confirmation (required on create)"),
 *     @OA\Property(property="roles", type="array", @OA\Items(type="string"), example={"admin", "superamin"}, description="Array of role Strings to assign to the user"),
 *     @OA\Property(property="status", type="integer", enum={0,1}, example=1, description="User status: 1=active, 0=inactive")
 * )
 */
class UserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $action = $this->route()->getActionMethod();
        $permission = 'admin.profiles.users.'.($action === 'store' ? 'create' : 'update');

        return $this->user()?->can($permission) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $method = $this->method();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required_if:action,store', 'string', 'min:8', 'confirmed'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'status' => 'nullable|integer|in:0,1',
        ];

        if (in_array($method, ['POST'])) {
            $rules['email'] = ['required', 'email', 'unique:users,email'];
        } elseif (in_array($method, ['PUT', 'PATCH'])) {
            $rules['email'] = ['email'];
        }

        return $rules;
    }
}
