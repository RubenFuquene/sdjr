<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="UpdateProductRequest",
 *   required={"product"},
 *   @OA\Property(
 *     property="product",
 *     type="object",
 *     @OA\Property(property="commerce_id", type="integer", example=1, description="ID of the commerce"),
 *     @OA\Property(property="product_category_id", type="integer", example=2, description="ID of the product category"),
 *     @OA\Property(property="title", type="string", maxLength=100, example="Café Premium", description="Product title"),
 *     @OA\Property(property="description", type="string", maxLength=255, nullable=true, example="Café de origen especial", description="Product description"),
 *     @OA\Property(property="product_type", type="string", enum={"single","package"}, example="single", description="Type of product (single/package)"),
 *     @OA\Property(property="original_price", type="number", format="float", example=100.00, description="Original price"),
 *     @OA\Property(property="discounted_price", type="number", format="float", nullable=true, example=80.00, description="Discounted price"),
 *     @OA\Property(property="quantity_total", type="integer", example=50, description="Total quantity"),
 *     @OA\Property(property="quantity_available", type="integer", example=50, description="Available quantity"),
 *     @OA\Property(property="expires_at", type="string", format="date-time", nullable=true, example="2026-12-31T23:59:59", description="Expiration date"),
 *     @OA\Property(property="status", type="string", maxLength=1, example="1", description="Status (1=Activo, 0=Inactivo)"),
 *   ),
 *   @OA\Property(
 *     property="commerce_branches",
 *     type="array",
 *     @OA\Items(type="integer", example=1, description="ID of a commerce branch")
 *   ),
 *   @OA\Property(
 *     property="package_items",
 *     type="array",
 *     @OA\Items(type="integer", example=10, description="ID of a product included in the package")
 *   )
 * )
 */
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
