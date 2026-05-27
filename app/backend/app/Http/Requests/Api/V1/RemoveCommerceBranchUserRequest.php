<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="RemoveCommerceBranchUserRequest",
 *     type="object",
 *     required={"user_id", "commerce_branch_id"},
 *
 *     @OA\Property(
 *         property="user_id",
 *         type="integer",
 *         example=1,
 *         description="User ID to remove from the branch"
 *     ),
 *     @OA\Property(
 *         property="commerce_branch_id",
 *         type="integer",
 *         example=1,
 *         description="Commerce branch ID where the user will be removed"
 *     )
 * )
 */
class RemoveCommerceBranchUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only commerce owners can remove users from branches
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
            'user_id' => ['required', 'integer', 'exists:users,id'],
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
            'user_id.required' => 'User ID is required',
            'user_id.exists' => 'User does not exist',
            'commerce_branch_id.required' => 'Commerce branch ID is required',
            'commerce_branch_id.exists' => 'Commerce branch does not exist',
        ];
    }
}
