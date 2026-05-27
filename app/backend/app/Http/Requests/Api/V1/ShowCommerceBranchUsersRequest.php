<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="ShowCommerceBranchUsersRequest",
 *     type="object",
 *     required={"commerce_branch_id"},
 *       description="Items per page for pagination (defaults to 15)"
 *     )
 * )
 */
class ShowCommerceBranchUsersRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Commerce owners, admins, and branch leaders can view branch users
        return $this->user()?->hasRole(['provider', 'branch_leader', 'admin', 'superadmin']) ?? false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [];
    }

    /**
     * Get the validated per_page value with default.
     */
    public function validatedPerPage(): int
    {
        return (int) ($this->input('per_page', 15));
    }
}
