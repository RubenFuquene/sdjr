<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="PatchProductStatusRequest",
 *   required={"status"},
 *
 *   @OA\Property(property="status", type="string", example="0", enum={"1","0"}, description="Product status (1=active, 0=inactive)")
 * )
 */
class PatchProductStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user || ! $user->can('provider.products.update')) {
            return false;
        }

        if ($user->hasAnyRole(['superadmin', 'admin'])) {
            return true;
        }

        $product = Product::find($this->route('id') ?? $this->route('product'));

        if (! $product) {
            return false;
        }

        return Commerce::query()
            ->where('id', $product->commerce_id)
            ->where('owner_user_id', $user->id)
            ->exists();
    }

    public function rules(): array
    {
        return [
            'status' => 'required|string|in:'.Constant::STATUS_ACTIVE.','.Constant::STATUS_INACTIVE,
        ];
    }
}
