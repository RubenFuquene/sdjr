<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="IndexCommerceBranchUserRequest",
 *     type="object",
 *     required={"commerce_id"},
 *
 *     @OA\Property(
 *         property="commerce_id",
 *         type="integer",
 *         example=1,
 *         description="Commerce ID used to list branch users"
 *     ),
 *     @OA\Property(
 *         property="per_page",
 *         type="integer",
 *         minimum=1,
 *         maximum=100,
 *         example=15,
 *         description="Items per page for pagination (defaults to 15)"
 *     )
 * )
 */
class IndexCommerceBranchUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Commerce owners and admins can list branch leaders
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
            'commerce_id' => ['required', 'integer', 'exists:commerces,id'],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    /**
     * Get the validated per_page value with default.
     */
    public function validatedPerPage(): int
    {
        return (int) ($this->input('per_page', 15));
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'commerce_id.required' => 'Commerce ID is required',
            'commerce_id.exists' => 'Commerce does not exist',
        ];
    }
}
