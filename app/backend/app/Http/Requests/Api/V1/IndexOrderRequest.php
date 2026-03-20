<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="IndexOrderRequest",
 *
 *     @OA\Property(property="status", type="string", nullable=true, example="pending", description="Filter orders by status"),
 *     @OA\Property(property="commerce_branch_id", type="integer", nullable=true, example=1, description="Filter orders by commerce branch ID")
 * )
 */
class IndexOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('customer.orders.index') || $this->user()?->can('provider.orders.index') ?? false;
    }

    public function prepareForValidation(): void
    {
        if (! $this->user()->isSuperadmin()) {
            $this->merge([
                'user_id' => $this->user()->id,
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'status' => 'nullable|string',
            'commerce_branch_id' => 'nullable|integer|exists:commerce_branches,id',
            'user_id' => 'nullable|integer|exists:users,id',
        ];
    }
}
