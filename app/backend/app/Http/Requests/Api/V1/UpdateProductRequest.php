<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('provider.products.update') ?? false;
    }

    public function rules(): array
    {
        return [
            
            'product.commerce_id' => ['required', 'integer', 'exists:commerces,id'],
            'product.product_category_id' => ['sometimes', 'integer', 'exists:product_categories,id'],
            'product.title' => ['sometimes', 'string', 'max:100'],
            'product.description' => ['nullable', 'string', 'max:255'],
            'product.product_type' => ['sometimes', 'string', 'in:single,package'],
            'product.original_price' => ['sometimes', 'numeric', 'min:0'],
            'product.discounted_price' => ['nullable', 'numeric', 'min:0'],
            'product.quantity_total' => ['sometimes', 'integer', 'min:0'],
            'product.quantity_available' => ['sometimes', 'integer', 'min:0'],
            'product.expires_at' => ['nullable', 'date'],
            'product.status' => ['sometimes', 'string', 'max:1'],

            'commerce_branches.*' => ['sometimes', 'integer', 'exists:commerce_branches,id'],

            //Fotos

            //Package
            'package_items.*' => ['sometimes', 'integer', 'exists:products,id'],
        ];
    }
}
