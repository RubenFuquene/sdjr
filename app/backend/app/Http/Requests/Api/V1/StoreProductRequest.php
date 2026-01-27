<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Constants\Constant;
use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('provider.products.create') ?? false;
    }

    public function rules(): array
    {
        return [
            
            'product.commerce_id' => ['required', 'integer', 'exists:commerces,id'],
            'product.product_category_id' => ['required', 'integer', 'exists:product_categories,id'],
            'product.title' => ['required', 'string', 'max:100'],
            'product.description' => ['nullable', 'string', 'max:255'],
            'product.product_type' => ['required', 'string', 'in:' . Constant::PRODUCT_TYPE_SINGLE . ',' . Constant::PRODUCT_TYPE_PACKAGE],
            'product.original_price' => ['required', 'numeric', 'min:0'],
            'product.discounted_price' => ['nullable', 'numeric', 'min:0'],
            'product.quantity_total' => ['required', 'integer', 'min:0'],
            'product.quantity_available' => ['required', 'integer', 'min:0'],
            'product.expires_at' => ['nullable', 'date'],
            'product.status' => ['sometimes', 'string', 'max:1'],

            'commerce_branch_ids.*' => ['required', 'integer', 'exists:commerce_branches,id'],

            //Fotos

            //Package
            'package_items.*' => ['sometimes', 'integer', 'exists:products,id'],
        ];
    }
}
