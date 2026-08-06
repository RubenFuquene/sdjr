<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Models\Commerce;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;

class ShowProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user || ! $user->can('provider.products.show')) {
            return false;
        }

        if ($user->hasAnyRole(['superadmin', 'admin'])) {
            return true;
        }

        // Reutilizado por show($id) y getPackageItems($product_package_id) —
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

    public function rules(): array
    {
        return [];
    }
}
