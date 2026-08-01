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
use Illuminate\Validation\Rule;

/**
 * @OA\Schema(
 *   schema="StoreProductRequest",
 *   required={"product"},
 *
 *   @OA\Property(
 *     property="product",
 *     type="object",
 *     required={"commerce_id","product_category_id","title","product_type","original_price"},
 *     @OA\Property(property="commerce_id", type="integer", example=1, description="ID of the commerce"),
 *     @OA\Property(property="product_category_id", type="integer", example=2, description="ID of the product category"),
 *     @OA\Property(property="title", type="string", maxLength=100, example="Café Premium", description="Product title"),
 *     @OA\Property(property="description", type="string", maxLength=255, nullable=true, example="Café de origen especial", description="Product description"),
 *     @OA\Property(property="product_type", type="string", enum={"single","package"}, example="single", description="Type of product (single/package)"),
 *     @OA\Property(property="original_price", type="number", format="float", example=100.00, description="Original price"),
 *     @OA\Property(property="discounted_price", type="number", format="float", nullable=true, example=80.00, description="Discounted price. Required and must be > 0 and <= original_price when product_type is 'single'; optional for 'package'."),
 *     @OA\Property(property="quantity_total", type="integer", example=50, description="Required only for product_type=package (how many packs can be sold). For 'single' the stock lives per branch in commerce_branches[].quantity_available (SCRUM-277)."),
 *     @OA\Property(property="quantity_available", type="integer", example=50, description="Required only for product_type=package. For 'single' the stock lives per branch in commerce_branches[].quantity_available (SCRUM-277)."),
 *     @OA\Property(property="expires_at", type="string", format="date-time", nullable=true, example="2026-12-31T23:59:59", description="Expiration date"),
 *     @OA\Property(property="status", type="string", maxLength=1, example="1", description="Status (1=Activo, 0=Inactivo)"),
 *     @OA\Property(property="photos", type="array", @OA\Items(ref="#/components/schemas/DocumentUploadResource")),
 *
 *   ),
 *   @OA\Property(
 *     property="commerce_branches",
 *     type="array",
 *     description="Sedes donde se asigna el producto, con su inventario y estado de publicación por sede (SCRUM-277)",
 *
 *     @OA\Items(
 *       type="object",
 *       required={"commerce_branch_id", "quantity_available"},
 *
 *       @OA\Property(property="commerce_branch_id", type="integer", example=1, description="ID of a commerce branch"),
 *       @OA\Property(property="quantity_available", type="integer", minimum=0, example=20, description="Stock available for this product in this branch"),
 *       @OA\Property(property="is_published", type="boolean", default=false, description="Whether the product is visible to customers in this branch. Requires quantity_available > 0.")
 *     )
 *   ),
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
            // SCRUM-335: obligatorio solo para productos individuales; los packs
            // mantienen el descuento opcional.
            'product.discounted_price' => [
                'nullable',
                Rule::requiredIf(fn () => $this->input('product.product_type') === Constant::PRODUCT_TYPE_SINGLE),
                'numeric',
                'min:0.01',
                'lte:product.original_price',
            ],
            // SCRUM-277 Fase 1: quantity_total/quantity_available a nivel de producto
            // solo tienen sentido para packs (cuántos packs se pueden vender). Para
            // single, el stock vive por sede en commerce_branches.*.quantity_available.
            'product.quantity_total' => [
                Rule::requiredIf(fn () => $this->input('product.product_type') === Constant::PRODUCT_TYPE_PACKAGE),
                'integer', 'min:0',
            ],
            'product.quantity_available' => [
                Rule::requiredIf(fn () => $this->input('product.product_type') === Constant::PRODUCT_TYPE_PACKAGE),
                'integer', 'min:0',
            ],
            'product.expires_at' => ['nullable', 'date'],
            'product.status' => ['sometimes', 'string', 'max:1'],

            'commerce_branches' => ['sometimes', 'array'],
            'commerce_branches.*.commerce_branch_id' => ['required', 'integer', 'exists:commerce_branches,id', 'distinct'],
            'commerce_branches.*.quantity_available' => ['required', 'integer', 'min:0'],
            'commerce_branches.*.is_published' => ['sometimes', 'boolean'],

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
            $this->validateCommerceBranches($validator);

            // TODO: For product_type=single, only the basic rules() (types/ranges/existence) are
            // enforced. No cross-field business validation is performed (e.g. discounted_price <
            // original_price, quantity_available <= quantity_total). Analyze in a future task.
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

            if ($packageItems->isEmpty() || $this->input('product.product_type') !== Constant::PRODUCT_TYPE_PACKAGE) {
                return;
            }

            $maxPacks = app(PackageAvailabilityCalculator::class)->maxPackageQuantity($packageItems);
            $requestedPacks = (int) $this->input('product.quantity_available');

            if ($requestedPacks > $maxPacks) {
                $validator->errors()->add(
                    'product.quantity_available',
                    "The requested quantity_available ({$requestedPacks}) exceeds the maximum packs available given current stock (max: {$maxPacks})."
                );
            }
        });
    }

    /**
     * SCRUM-277 Fase 1 (Tareas 3.4 y 3.8): no se puede publicar (is_published=true)
     * una sede sin inventario cargado, y los packs no se pueden publicar en
     * ninguna sede todavía — Opción A: bloqueados hasta que la Fase 2 les dé su
     * propio cálculo de disponibilidad por sede. Hoy nada es publicable (el bug
     * original de SCRUM-277), así que este bloqueo no quita funcionalidad
     * existente a los packs.
     */
    private function validateCommerceBranches(Validator $validator): void
    {
        $productType = $this->input('product.product_type');

        foreach ($this->input('commerce_branches', []) as $index => $branch) {
            if (! ($branch['is_published'] ?? false)) {
                continue;
            }

            if ($productType === Constant::PRODUCT_TYPE_PACKAGE) {
                $validator->errors()->add(
                    "commerce_branches.{$index}.is_published",
                    'Packages cannot be published yet.'
                );

                continue;
            }

            if ((int) ($branch['quantity_available'] ?? 0) === 0) {
                $validator->errors()->add(
                    "commerce_branches.{$index}.is_published",
                    'Cannot publish a branch with zero available quantity.'
                );
            }
        }
    }
}
