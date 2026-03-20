<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\Order;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *     schema="DeleteOrderRequest",
 *     required={"id"},
 *
 *     @OA\Property(property="id", type="integer", example=123, description="Order identifier received as route parameter")
 * )
 */
class DeleteOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        if (! $user) {
            return false;
        }

        $canDeleteProvider = $user->can('provider.orders.delete');
        if (! $canDeleteProvider) {
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

    public function rules(): array
    {
        return [
            // ID handled via route param
        ];
    }
}
