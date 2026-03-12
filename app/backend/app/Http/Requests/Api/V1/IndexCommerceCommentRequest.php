<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

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
    public function authorize(): bool
    {
        return $this->user()?->can('provider.comments.index') ?? false;
    }

    public function rules(): array
    {
        return [];
    }

    public function validatedPerPage(): int
    {
        return (int) ($this->input('per_page', 15));
    }

    /**
     * Get validated filters.
     */
    public function validatedFilters(): array
    {
        return $this->only(['created_by', 'priority', 'status']);
    }
}
