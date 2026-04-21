<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="StoreProductRequest",
 *   required={"product"},
 *
 *   @OA\Property(
 *     property="product",
 *     type="object",
 *     required={"commerce_id","product_category_id","title","product_type","original_price","quantity_total","quantity_available"},
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
 *     @OA\Property(property="photos", type="array", @OA\Items(ref="#/components/schemas/DocumentUploadResource")),
 *
 *   ),
 *   @OA\Property(
 *     property="commerce_branch_ids",
 *     type="array",
 *
 *     @OA\Items(type="integer", example=1, description="ID of a commerce branch")
 *   ),
 *
 *   @OA\Property(
 *     property="package_items",
 *     type="array",
 *     description="Array of products included in this package",
 *
 *     @OA\Items(
 *       type="object",
 *       required={"product_id", "quantity"},
 *
 *       @OA\Property(
 *         property="product_id",
 *         type="integer",
 *         example=10,
 *         description="ID of the product to include"
 *       ),
 *       @OA\Property(
 *         property="quantity",
 *         type="integer",
 *         minimum=1,
 *         example=2,
 *         description="Quantity of this product in the package"
 *       )
 *     )
 *   )
 * )
 */
class StoreProductRequest extends FormRequest
{
    protected $productService;

    public function __construct(ProductService $productService)
    {
        $this->productService = $productService;
    }

    public function authorize(): bool
    {
        return $this->productService->validateStoreRequest($this->user(), $this->all());
    }

    public function rules(): array
    {
        return [

            'product.commerce_id' => ['required', 'integer', 'exists:commerces,id'],
            'product.product_category_id' => ['required', 'integer', 'exists:product_categories,id'],
            'product.title' => ['required', 'string', 'max:100'],
            'product.description' => ['nullable', 'string', 'max:255'],
            'product.product_type' => ['required', 'string', 'in:'.Constant::PRODUCT_TYPE_SINGLE.','.Constant::PRODUCT_TYPE_PACKAGE],
            'product.original_price' => ['required', 'numeric', 'min:0'],
            'product.discounted_price' => ['nullable', 'numeric', 'min:0'],
            'product.quantity_total' => ['required', 'integer', 'min:0'],
            'product.quantity_available' => ['required', 'integer', 'min:0'],
            'product.expires_at' => ['nullable', 'date'],
            'product.status' => ['sometimes', 'string', 'max:1'],

            'commerce_branch_ids.*' => ['required', 'integer', 'exists:commerce_branches,id'],

            // Fotos
            'photos' => ['array', 'max:'.Constant::MAX_PHOTOS_PER_PRODUCT],
            'photos.*.file_name' => ['required', 'string', 'max:255'],
            'photos.*.mime_type' => ['required', 'string', 'in:'.implode(',', Constant::ALLOWED_PHOTO_EXTENSIONS)],
            'photos.*.file_size_bytes' => ['required', 'integer', 'min:1', 'max:'.Constant::ALLOWED_PHOTO_SIZE_BYTES],
            'photos.*.versioning_enabled' => ['string'],
            'photos.*.metadata' => ['nullable', 'array'],

            // Package
            'package_items' => ['sometimes', 'array'],
            'package_items.*.product_id' => ['required', 'integer', 'exists:products,id', 'distinct'],
            'package_items.*.quantity' => ['required', 'integer', 'min:1'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            if ($this->has('package_items')) {
                foreach ($this->input('package_items', []) as $index => $item) {
                    if (isset($item['product_id']) && isset($item['quantity'])) {
                        $product = Product::find($item['product_id']);
                        if ($product) {
                            if ($item['quantity'] > $product->quantity_available) {
                                $validator->errors()->add(
                                    "package_items.{$index}.quantity",
                                    "The quantity cannot exceed the available quantity ({$product->quantity_available}) of product '{$product->title}'."
                                );
                            }
                        }
                    }
                }
            }
        });
    }
}
