<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\CommerceBranch;
use App\Traits\AuthorizesCommerceOwnership;
use Illuminate\Foundation\Http\FormRequest;

/**
 * SCRUM-343: `GET /products/commerce/branch/{branch_id}` estaba bajo
 * auth:sanctum sin ningún FormRequest. Misma decisión que
 * ProductsByCommerceRequest: privado, propiedad derivada de la sucursal.
 */
class ProductsByCommerceBranchRequest extends FormRequest
{
    use AuthorizesCommerceOwnership;

    public function authorize(): bool
    {
        if (! $this->user()?->can('provider.products.index')) {
            return false;
        }

        $branchId = (int) ($this->route('branch_id') ?? 0);
        if ($branchId <= 0) {
            return false;
        }

        $commerceId = CommerceBranch::query()->whereKey($branchId)->value('commerce_id');

        return $commerceId !== null && $this->userCanAccessCommerce((int) $commerceId);
    }

    public function rules(): array
    {
        return [];
    }
}
