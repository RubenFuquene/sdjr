<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreCommerceBranchUserRequest",
 *     type="object",
 *     required={"name", "last_name", "email", "phone", "commerce_branch_id"},
 *
 *     @OA\Property(
 *         property="name",
 *         type="string",
 *         maxLength=255,
 *         example="Juan",
 *         description="Branch leader first name"
 *     ),
 *     @OA\Property(
 *         property="last_name",
 *         type="string",
 *         maxLength=255,
 *         example="Perez",
 *         description="Branch leader last name"
 *     ),
 *     @OA\Property(
 *         property="email",
 *         type="string",
 *         format="email",
 *         maxLength=255,
 *         example="juan.perez@example.com",
 *         description="Unique email for the branch leader"
 *     ),
 *     @OA\Property(
 *         property="phone",
 *         type="string",
 *         maxLength=20,
 *         example="3001234567",
 *         description="Branch leader phone number"
 *     ),
 *     @OA\Property(
 *         property="commerce_branch_id",
 *         type="integer",
 *         example=1,
 *         description="Commerce branch ID where the new user will be assigned"
 *     )
 * )
 */
class StoreCommerceBranchUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only commerce owners can create branch leaders
        // Additional authorization happens in the controller/service
        return $this->user()?->hasRole(['provider', 'admin', 'superadmin']) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:20'],
            'commerce_branch_id' => ['required', 'integer', 'exists:commerce_branches,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Name is required',
            'last_name.required' => 'Last name is required',
            'email.required' => 'Email is required',
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'Email is already registered',
            'phone.required' => 'Phone is required',
            'commerce_branch_id.required' => 'Commerce Branch ID is required',
            'commerce_branch_id.exists' => 'Commerce Branch does not exist',
        ];
    }
}
