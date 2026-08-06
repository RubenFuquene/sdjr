<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\Commerce;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="DeleteProductRequest",
 *   required={"id"},
 *
 *   @OA\Property(property="id", type="integer", example=1, description="ID of the product to delete")
 * )
 */
class DeleteProductRequest extends FormRequest
{
    /**
     * Authorize the request based on user permissions and ownership.
     */
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user || ! $user->can('provider.products.delete')) {
            return false;
        }

        if ($user->hasAnyRole(['superadmin', 'admin'])) {
            return true;
        }

        // Reutilizado por destroy($id) y deletePackageItems($product_package_id) —
        // ambos identifican un Product, distinto nombre de parámetro de ruta.
        $product = Product::find(
            $this->route('id') ?? $this->route('product') ?? $this->route('product_package_id')
        );

        if (! $product) {
            return false;
        }

        return Commerce::query()
            ->where('id', $product->commerce_id)
            ->where('owner_user_id', $user->id)
            ->exists();
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [];
    }
}
