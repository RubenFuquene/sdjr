<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="IndexCommercePayoutMethodRequest",
 *   type="object",
 *
 *   @OA\Property(property="per_page", type="integer", example=15),
 *   @OA\Property(property="page", type="integer", example=1),
 *   @OA\Property(property="type", type="string", example="bank"),
 *   @OA\Property(property="owner_id", type="integer", example=1),
 *   @OA\Property(property="account_number", type="string", example="1234567890"),
 *   @OA\Property(property="status", type="string", maxLength=1, example="1")
 * )
 */
class IndexCommercePayoutMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permiso: commerce_payout_methods.index
        return $this->user()?->can('provider.commerce_payout_methods.index') ?? false;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'type' => ['sometimes', 'string'],
            'owner_id' => ['sometimes', 'integer'],
            'account_number' => ['sometimes', 'string'],
            'status' => ['sometimes', 'string', 'max:1'],
        ];
    }

    public function validatedFilters(): array
    {
        return $this->only(['type', 'owner_id', 'account_number', 'status']);
    }

    public function validatedPerPage(): int
    {
        return (int) ($this->input('per_page', 15));
    }

    public function validatedPage(): int
    {
        return (int) ($this->input('page', 1));
    }
}
