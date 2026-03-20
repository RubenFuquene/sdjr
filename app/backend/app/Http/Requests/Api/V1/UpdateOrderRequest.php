<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Constants\Constant;
use App\Models\Order;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="UpdateOrderRequest",
 *     required={"status"},
 *
 *     @OA\Property(property="user_id", type="integer", nullable=true, example=15, description="User identifier (optional; injected for non-superadmin users)"),
 *     @OA\Property(property="status", type="string", example="confirmed", enum={"pending","confirmed","preparing","ready","delivered","cancelled"}, description="New order status")
 * )
 */
class UpdateOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        $canUpdateAsProvider = $user->can('provider.orders.update');
        if (! $canUpdateAsProvider) {
            return false;
        }

        if ($user->hasAnyRole(['superadmin', 'admin'])) {
            return true;
        }

        $orderId = (int) ($this->route('id') ?? $this->route('order') ?? 0);
        if ($orderId <= 0) {
            return false;
        }

        return Order::query()
            ->whereKey($orderId)
            ->where(function (Builder $query) use ($user): void {
                $query
                    ->where('user_id', $user->id)
                    ->orWhereHas('commerceBranch.commerce', function (Builder $commerceQuery) use ($user): void {
                        $commerceQuery->where('owner_user_id', $user->id);
                    });
            })
            ->exists();
    }

    public function prepareForValidation(): void
    {
        if (! $this->user()->isSuperadmin()) {
            $this->merge([
                'user_id' => $this->user()->id,
                'commerce_branch_id' => $this->user()->userCommerceBranch(),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'user_id' => 'nullable|integer|exists:users,id',
            'commerce_branch_id' => 'nullable|exists:commerce_branches,id',
            'status' => 'required|in:'.implode(',', [
                Constant::ORDER_STATUS_PENDING,
                Constant::ORDER_STATUS_CONFIRMED,
                Constant::ORDER_STATUS_PREPARING,
                Constant::ORDER_STATUS_READY,
                Constant::ORDER_STATUS_DELIVERED,
                Constant::ORDER_STATUS_CANCELLED,
            ]),
        ];
    }
}
