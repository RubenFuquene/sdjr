<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Constants\Constant;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="StoreOrderRequest",
 *     required={"commerce_branch_id", "items"},
 *
 *     @OA\Property(property="user_id", type="integer", nullable=true, example=5, description="User identifier (automatically set for non-superadmin users)"),
 *     @OA\Property(property="commerce_branch_id", type="integer", nullable=true, example=1, description="Commerce branch identifier (automatically set for non-superadmin users)"),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *         minItems=1,
 *         maxItems=50,
 *         description="Order items list",
 *
 *         @OA\Items(
 *             type="object",
 *             required={"product_id", "quantity"},
 *
 *             @OA\Property(property="product_id", type="integer", example=10, description="Product identifier"),
 *             @OA\Property(property="quantity", type="integer", minimum=1, example=2, description="Requested quantity")
 *         )
 *     )
 * )
 */
class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('customer.orders.create') ?? false;
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
            'user_id' => 'nullable|integer|exists:users,id',
            'commerce_branch_id' => 'required|exists:commerce_branches,id',
            'items' => 'required|array|min:1|max:'.Constant::MAX_ORDER_ITEMS,
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:'.Constant::MIN_ORDER_QUANTITY,
        ];
    }
}
