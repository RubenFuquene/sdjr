<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="MyFavoriteCommerceBranchesRequest",
 *     @OA\Property(property="limit", type="integer", minimum=1, maximum=10, example=5, description="Top favorites limit")
 * )
 */
class MyFavoriteCommerceBranchesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('customer.commerce-branches.my-favorites') ?? false;
    }

    public function rules(): array
    {
        return [
            'limit' => ['nullable', 'integer', 'min:1', 'max:10'],
        ];
    }

    public function validatedLimit(): int
    {
        return (int) ($this->validated()['limit'] ?? 5);
    }
}