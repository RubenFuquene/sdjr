<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Constants\Constant;
use App\Traits\AuthorizesCommerceOwnership;
use Illuminate\Foundation\Http\FormRequest;

/**
 * IndexCommerceCommentRequest
 *
 * @OA\Schema(
 *     schema="IndexCommerceCommentRequest",
 *
 *     @OA\Property(property="commerce_id", type="integer", example=10),
 *     @OA\Property(property="per_page", type="integer", example=15)
 * )
 */
class IndexCommerceCommentRequest extends FormRequest
{
    use AuthorizesCommerceOwnership;

    public function authorize(): bool
    {
        return ($this->user()?->can('provider.comments.index') ?? false)
            && $this->userCanAccessCommerce();
    }

    public function rules(): array
    {
        return [
            'created_by' => ['sometimes', 'integer', 'exists:users,id'],
            'priority_type_id' => ['sometimes', 'integer', 'exists:priority_types,id'],
            'color' => ['sometimes', 'string', 'max:20'],
            'comment_type' => ['sometimes', 'string', 'in:'.implode(',', array_keys(Constant::COMMENT_TYPE_ARRAY))],
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function validatedPerPage(): int
    {
        return (int) ($this->input('per_page', Constant::DEFAULT_PER_PAGE));
    }

    /**
     * Get validated filters supported by the service.
     */
    public function validatedFilters(): array
    {
        return $this->only(['created_by', 'priority_type_id', 'color', 'comment_type']);
    }
}
