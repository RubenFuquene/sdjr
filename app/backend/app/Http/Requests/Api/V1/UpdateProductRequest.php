<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\Product;
use App\Services\PackageAvailabilityCalculator;
use App\Services\ProductService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="UpdateProductRequest",
 *   required={"product"},
 *
 *   @OA\Property(
 *     property="product",
 *     type="object",
 *     @OA\Property(property="commerce_id", type="integer", example=1, description="ID of the commerce"),
 *     @OA\Property(property="product_category_id", type="integer", example=2, description="ID of the product category"),
 *     @OA\Property(property="title", type="string", maxLength=100, example="Café Premium", description="Product title"),
 *     @OA\Property(property="description", type="string", maxLength=255, nullable=true, example="Café de origen especial", description="Product description"),
 *     @OA\Property(property="product_type", type="string", enum={"single","package"}, example="single", description="Type of product (single/package)"),
 *     @OA\Property(property="original_price", type="number", format="float", example=100.00, description="Original price"),
 *     @OA\Property(property="discounted_price", type="number", format="float", nullable=true, example=80.00, description="Discounted price. Required and must be > 0 and <= original_price when the product (new or existing) is of type 'single'; optional for 'package'."),
 *     @OA\Property(property="quantity_total", type="integer", example=50, description="Total quantity"),
 *     @OA\Property(property="quantity_available", type="integer", example=50, description="Available quantity"),
 *     @OA\Property(property="expires_at", type="string", format="date-time", nullable=true, example="2026-12-31T23:59:59", description="Expiration date"),
 *     @OA\Property(property="status", type="string", maxLength=1, example="1", description="Status (1=Activo, 0=Inactivo)"),
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
class UpdateProductRequest extends FormRequest
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
            'product.product_category_id' => ['sometimes', 'integer', 'exists:product_categories,id'],
            'product.title' => ['sometimes', 'string', 'max:100'],
            'product.description' => ['nullable', 'string', 'max:255'],
            'product.product_type' => ['sometimes', 'string', 'in:single,package'],
            'product.original_price' => ['sometimes', 'numeric', 'min:0'],
            // SCRUM-335: la obligatoriedad (solo para product_type=single) y la
            // comparación con original_price se validan en withValidator(), no aquí,
            // porque en edición ambos valores pueden venir de la BD (campo no
            // enviado) en vez del payload — ver validateDiscountedPrice().
            'product.discounted_price' => ['nullable', 'numeric', 'min:0.01'],
            'product.quantity_total' => ['sometimes', 'integer', 'min:0'],
            'product.quantity_available' => ['sometimes', 'integer', 'min:0'],
            'product.expires_at' => ['nullable', 'date'],
            'product.status' => ['sometimes', 'string', 'max:1'],

            // La regla del array padre es necesaria además de la de sus elementos:
            // sin ella, Laravel omite la clave por completo de validated() cuando
            // el array viene vacío, y no hay forma de distinguir "ausente" (no
            // tocar la relación) de "presente y vacío a propósito" (limpiarla).
            'commerce_branch_ids' => ['sometimes', 'array'],
            'commerce_branch_ids.*' => ['sometimes', 'integer', 'exists:commerce_branches,id'],

            // Fotos

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
            $routeParameters = $this->route()?->parameters() ?? [];
            $currentPackageId = $routeParameters !== [] ? (int) reset($routeParameters) : null;
            $existingPackage = $currentPackageId ? Product::find($currentPackageId) : null;

            $this->validateDiscountedPrice($validator, $existingPackage);

            // TODO: For product_type=single, quantity_available <= quantity_total is not
            // enforced. Analyze in a future task (discounted_price <= original_price is
            // already covered by validateDiscountedPrice() above, SCRUM-335).
            if (! $this->has('package_items')) {
                return;
            }

            $packageItems = collect();

            foreach ($this->input('package_items', []) as $index => $item) {
                if (! isset($item['product_id'], $item['quantity'])) {
                    continue;
                }

                $product = Product::find($item['product_id']);

                if (! $product) {
                    continue;
                }

                if ($product->product_type !== Constant::PRODUCT_TYPE_SINGLE) {
                    $validator->errors()->add(
                        "package_items.{$index}.product_id",
                        "The product '{$product->title}' must be of type 'single' to be included in a package."
                    );

                    continue;
                }

                if ($item['quantity'] > $product->quantity_available) {
                    $validator->errors()->add(
                        "package_items.{$index}.quantity",
                        "The quantity cannot exceed the available quantity ({$product->quantity_available}) of product '{$product->title}'."
                    );

                    continue;
                }

                $packageItems->push(['product' => $product, 'quantity' => (int) $item['quantity']]);
            }

            $productType = $this->input('product.product_type', $existingPackage?->product_type);

            if ($packageItems->isEmpty() || $productType !== Constant::PRODUCT_TYPE_PACKAGE) {
                return;
            }

            $maxPacks = app(PackageAvailabilityCalculator::class)->maxPackageQuantity($packageItems, $currentPackageId);
            $requestedPacks = (int) $this->input('product.quantity_available', $existingPackage?->quantity_available ?? 0);

            if ($requestedPacks > $maxPacks) {
                $validator->errors()->add(
                    'product.quantity_available',
                    "The requested quantity_available ({$requestedPacks}) exceeds the maximum packs available given current stock (max: {$maxPacks})."
                );
            }
        });
    }

    /**
     * SCRUM-335: discounted_price es obligatorio (y debe ser <= original_price) solo
     * para productos individuales. En edición el payload puede omitir cualquiera de
     * los dos campos (ediciones parciales), así que se resuelve el valor efectivo
     * combinando lo enviado con el producto existente en BD antes de validar —
     * de lo contrario, cualquier edición que no toque el descuento fallaría aunque
     * el producto ya tenga uno válido guardado.
     */
    private function validateDiscountedPrice(Validator $validator, ?Product $existingProduct): void
    {
        $productType = $this->input('product.product_type', $existingProduct?->product_type);

        if ($productType !== Constant::PRODUCT_TYPE_SINGLE) {
            return;
        }

        $discountedPrice = $this->has('product.discounted_price')
            ? $this->input('product.discounted_price')
            : $existingProduct?->discounted_price;

        if ($discountedPrice === null || $discountedPrice === '') {
            $validator->errors()->add(
                'product.discounted_price',
                'The discounted_price field is required for single products.'
            );

            return;
        }

        $originalPrice = $this->input('product.original_price', $existingProduct?->original_price);

        if ($originalPrice !== null && (float) $discountedPrice > (float) $originalPrice) {
            $validator->errors()->add(
                'product.discounted_price',
                'The discounted_price field must be less than or equal to original_price.'
            );
        }
    }
}
