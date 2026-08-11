<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use App\Constants\Constant;
use App\Enums\FiscalCode;
use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\Product;
use App\Services\FiscalCodeResolver;
use App\Services\PackageCompositionValidator;
use App\Services\ProductService;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Enum;

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
 *       @OA\Property(property="quantity_available", type="integer", minimum=0, example=20, description="For product_type=single: stock available in this branch. For product_type=package: packs committed in this branch (SCRUM-361)."),
 *       @OA\Property(property="is_published", type="boolean", default=false, description="Whether the product is visible to customers in this branch. Requires quantity_available > 0, and for packages also requires enough component stock in this branch.")
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
            // SCRUM-370: un pack no elige categoría — se deriva de sus
            // componentes (ProductService). Solo obligatoria para 'single'.
            'product.product_category_id' => [
                Rule::requiredIf(fn () => $this->input('product.product_type') !== Constant::PRODUCT_TYPE_PACKAGE),
                'integer',
                'exists:product_categories,id',
            ],
            // SCRUM-362: el aliado nunca digita porcentaje — la pertenencia al
            // conjunto permitido del comercio y la obligatoriedad para
            // 'single' se validan en withValidator() (validateFiscalCode()),
            // aquí solo se garantiza que sea un valor del enum.
            'product.fiscal_code' => ['nullable', new Enum(FiscalCode::class)],
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
            $this->validateSingleBranchPolicyForPackages($validator);
            $this->validatePackageComposition($validator);
            $this->validateFiscalCode($validator);
            $this->validatePackageCategoryIsDerivable($validator);
        });
    }

    /**
     * SCRUM-362: fiscal_code es obligatorio para 'single' (los packs no
     * llevan uno propio — D4) y debe estar dentro del conjunto que
     * FiscalCodeResolver permite para el comercio dueño del producto, no
     * solo ser un valor válido del enum.
     */
    private function validateFiscalCode(Validator $validator): void
    {
        if ($this->input('product.product_type') !== Constant::PRODUCT_TYPE_SINGLE) {
            return;
        }

        $fiscalCode = $this->input('product.fiscal_code');

        if ($fiscalCode === null || $fiscalCode === '') {
            $validator->errors()->add('product.fiscal_code', 'The fiscal_code field is required for single products.');

            return;
        }

        $fiscalCodeEnum = FiscalCode::tryFrom($fiscalCode);

        if (! $fiscalCodeEnum) {
            return; // Ya reportado por la regla Enum de rules().
        }

        $commerce = Commerce::find($this->input('product.commerce_id'));

        if ($commerce && ! app(FiscalCodeResolver::class)->isAllowed($commerce, $fiscalCodeEnum)) {
            $validator->errors()->add(
                'product.fiscal_code',
                'This fiscal_code is not allowed for the commerce establishment type.'
            );
        }
    }

    /**
     * SCRUM-370: la categoría del pack se deriva del componente de mayor
     * valor (ProductService), nunca se pregunta. Pero product_category_id
     * sigue siendo NOT NULL en BD, así que un pack necesita al menos un
     * componente válido para poder crearse — si no, no hay de dónde derivar.
     */
    private function validatePackageCategoryIsDerivable(Validator $validator): void
    {
        if ($this->input('product.product_type') !== Constant::PRODUCT_TYPE_PACKAGE) {
            return;
        }

        $hasValidComponent = collect($this->input('package_items', []))
            ->contains(fn ($item) => isset($item['product_id']) && Product::find($item['product_id']));

        if (! $hasValidComponent) {
            $validator->errors()->add(
                'package_items',
                'A package needs at least one valid component to determine its category.'
            );
        }
    }

    /**
     * Ajuste funcional 2026-08-03: un pack se ofrece en una sola sede. Se
     * valida aparte de validatePackageComposition() porque debe aplicar
     * aunque el payload no toque package_items (ej. asignar una segunda
     * sede a un pack ya armado).
     */
    private function validateSingleBranchPolicyForPackages(Validator $validator): void
    {
        if ($this->input('product.product_type') !== Constant::PRODUCT_TYPE_PACKAGE) {
            return;
        }

        $branchIds = collect($this->input('commerce_branches', []))
            ->pluck('commerce_branch_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        if (app(PackageCompositionValidator::class)->exceedsSingleBranchPolicy($branchIds)) {
            $validator->errors()->add(
                'commerce_branches',
                'A package can only be offered in a single branch. Use "Duplicate" to offer it in another branch.'
            );
        }
    }

    /**
     * SCRUM-361 (Tarea 4, SCRUM-323): cada componente de un pack debe tener
     * inventario asignado en cada sede a la que se asigna el pack, y el
     * compromiso solicitado por sede no puede exceder lo que esos
     * componentes soportan en esa misma sede.
     */
    private function validatePackageComposition(Validator $validator): void
    {
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

            if ($product->fiscal_code === FiscalCode::PendingReview) {
                $validator->errors()->add(
                    "package_items.{$index}.product_id",
                    __('products.package_composition.pending_review', ['title' => $product->title])
                );

                continue;
            }

            if ($product->product_type !== Constant::PRODUCT_TYPE_SINGLE) {
                $validator->errors()->add(
                    "package_items.{$index}.product_id",
                    __('products.package_composition.not_single_type', ['title' => $product->title])
                );

                continue;
            }

            $packageItems->push(['index' => $index, 'product' => $product, 'quantity' => (int) $item['quantity']]);
        }

        if ($packageItems->isEmpty() || $this->input('product.product_type') !== Constant::PRODUCT_TYPE_PACKAGE) {
            return;
        }

        $branchIds = collect($this->input('commerce_branches', []))
            ->pluck('commerce_branch_id')
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        $compositionValidator = app(PackageCompositionValidator::class);

        foreach ($compositionValidator->componentsMissingBranchStock($packageItems, $branchIds) as $missing) {
            $branchName = CommerceBranch::find($missing['branchId'])?->name ?? "branch #{$missing['branchId']}";

            $validator->errors()->add(
                "package_items.{$missing['index']}.product_id",
                __('products.package_composition.missing_stock', [
                    'title' => $missing['product']->title,
                    'branch' => $branchName,
                ])
            );
        }

        $requestedByBranch = collect($this->input('commerce_branches', []))
            ->mapWithKeys(fn ($branch) => [(int) $branch['commerce_branch_id'] => (int) ($branch['quantity_available'] ?? 0)]);

        $branchIndexById = collect($this->input('commerce_branches', []))
            ->mapWithKeys(fn ($branch, $index) => [(int) $branch['commerce_branch_id'] => $index]);

        foreach ($compositionValidator->maxPacksByBranch($packageItems, $branchIds, null) as $branchId => $maxPacks) {
            $requested = $requestedByBranch->get($branchId, 0);

            if ($requested > $maxPacks) {
                // SCRUM-227: se resuelve el nombre de sede aquí también (antes
                // solo lo hacía la validación de missing_stock) para unificar
                // el mensaje con el de UpdateProductRequest, que ya lo incluía.
                $branchName = CommerceBranch::find($branchId)?->name ?? "branch #{$branchId}";

                $validator->errors()->add(
                    "commerce_branches.{$branchIndexById->get($branchId)}.quantity_available",
                    __('products.package_composition.max_packs_exceeded', [
                        'requested' => $requested,
                        'branch' => $branchName,
                        'max' => $maxPacks,
                    ])
                );
            }
        }

        $this->validatePackagePriceCeiling($validator, $packageItems, $this->input('product.original_price'));
    }

    /**
     * Ticket derivado de SCRUM-361/323 (2026-08-04): el precio del pack no
     * se acepta a ciegas del cliente aunque la interfaz ya lo calcule y
     * deshabilite su edición — el techo es la suma de los precios de venta
     * VIGENTES (con descuento) de sus componentes, no de sus precios de
     * lista, para que un pack sin descuento propio nunca cueste más que
     * comprar las partes sueltas.
     */
    private function validatePackagePriceCeiling(Validator $validator, \Illuminate\Support\Collection $packageItems, mixed $submittedOriginalPrice): void
    {
        $expected = round(
            $packageItems->sum(fn ($item) => $item['product']->currentSalePrice() * $item['quantity']),
            2
        );
        $submitted = round((float) $submittedOriginalPrice, 2);

        if (abs($submitted - $expected) > 0.01) {
            $validator->errors()->add(
                'product.original_price',
                __('products.package_composition.price_ceiling', ['expected' => $expected])
            );
        }
    }

    /**
     * No se puede publicar (is_published=true) una sede sin inventario
     * cargado. Para packs, esa misma condición ya cubre "sin componentes
     * suficientes": validatePackageComposition() rechaza cualquier
     * quantity_available que exceda la capacidad real, así que si el pack
     * llega hasta aquí con quantity_available > 0, sus componentes ya la
     * soportan (SCRUM-361, Tarea 5.3 — levanta el guard "packages cannot be
     * published yet" de la Fase 1).
     */
    private function validateCommerceBranches(Validator $validator): void
    {
        foreach ($this->input('commerce_branches', []) as $index => $branch) {
            if (! ($branch['is_published'] ?? false)) {
                continue;
            }

            // SCRUM-362: un producto sin clasificar no se publica — solo
            // aplica a 'single', un pack nunca tiene fiscal_code propio.
            if ($this->input('product.fiscal_code') === FiscalCode::PendingReview->value) {
                $validator->errors()->add(
                    "commerce_branches.{$index}.is_published",
                    'Cannot publish a product with a pending fiscal classification.'
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
