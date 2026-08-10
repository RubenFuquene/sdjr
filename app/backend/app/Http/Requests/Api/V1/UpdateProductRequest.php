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
use Illuminate\Validation\Rules\Enum;

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
 *     @OA\Property(property="expires_at", type="string", format="date-time", nullable=true, example="2026-12-31T23:59:59", description="Expiration date"),
 *     @OA\Property(property="status", type="string", maxLength=1, example="1", description="Status (1=Activo, 0=Inactivo)"),
 *   ),
 *   @OA\Property(
 *     property="commerce_branches",
 *     type="array",
 *     description="Sedes donde se asigna el producto, con su inventario y estado de publicación por sede (SCRUM-277). Ausente = no tocar la asignación existente; presente = reemplaza la asignación completa.",
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
 *   ),
 *   @OA\Property(property="confirm_package_adjustments", type="boolean", default=false, description="SCRUM-361: confirms applying automatic adjustments to packs affected by a stock decrease in this product. Without it, a 409 is returned listing the affected packs instead of applying the change."),
 *   @OA\Property(property="confirm_fiscal_reclassification", type="boolean", default=false, description="SCRUM-362: confirms unpublishing this product's branches and any packs that contain it when reclassifying to otro_verificar. Without it, a 409 is returned listing the affected branches/packs instead of applying the change.")
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
            'product.fiscal_code' => ['sometimes', 'nullable', new Enum(FiscalCode::class)],
            'product.title' => ['sometimes', 'string', 'max:100'],
            'product.description' => ['nullable', 'string', 'max:255'],
            'product.product_type' => ['sometimes', 'string', 'in:single,package'],
            'product.original_price' => ['sometimes', 'numeric', 'min:0'],
            // SCRUM-335: la obligatoriedad (solo para product_type=single) y la
            // comparación con original_price se validan en withValidator(), no aquí,
            // porque en edición ambos valores pueden venir de la BD (campo no
            // enviado) en vez del payload — ver validateDiscountedPrice().
            'product.discounted_price' => ['nullable', 'numeric', 'min:0.01'],
            'product.expires_at' => ['nullable', 'date'],
            'product.status' => ['sometimes', 'string', 'max:1'],

            // La regla del array padre es necesaria además de la de sus elementos:
            // sin ella, Laravel omite la clave por completo de validated() cuando
            // el array viene vacío, y no hay forma de distinguir "ausente" (no
            // tocar la relación) de "presente y vacío a propósito" (limpiarla).
            'commerce_branches' => ['sometimes', 'array'],
            'commerce_branches.*.commerce_branch_id' => ['required', 'integer', 'exists:commerce_branches,id', 'distinct'],
            'commerce_branches.*.quantity_available' => ['required', 'integer', 'min:0'],
            'commerce_branches.*.is_published' => ['sometimes', 'boolean'],

            // Fotos

            // Package
            'package_items' => ['sometimes', 'array'],
            'package_items.*.product_id' => ['required', 'integer', 'exists:products,id', 'distinct'],
            'package_items.*.quantity' => ['required', 'integer', 'min:1'],

            // SCRUM-361, Tarea 3.3-3.4: si bajar el stock de un componente (o
            // quitarle una sede) deja packs sobre-comprometidos, la API responde
            // 409 salvo que esta bandera venga en true — entonces aplica el
            // ajuste junto con la edición, atómicamente.
            'confirm_package_adjustments' => ['sometimes', 'boolean'],

            // SCRUM-362 (D9): reclasificar a otro_verificar despublica en
            // cascada sedes y packs — 409 salvo que venga en true.
            'confirm_fiscal_reclassification' => ['sometimes', 'boolean'],
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
            $this->validateCommerceBranches($validator, $existingPackage);
            $this->validateSingleBranchPolicyForPackages($validator, $existingPackage);
            $this->validatePackageComposition($validator, $existingPackage, $currentPackageId);
            $this->validateFiscalCode($validator, $existingPackage);
            $this->validatePackageCategoryIsDerivable($validator, $existingPackage);
        });
    }

    /**
     * SCRUM-362: mismo criterio que validateDiscountedPrice() — valor
     * efectivo (payload ?? BD), porque una edición parcial puede no tocar
     * fiscal_code y el producto ya tiene uno guardado. Fuera de rules()
     * porque también valida pertenencia al conjunto permitido del comercio
     * (FiscalCodeResolver), no solo la forma del valor.
     */
    private function validateFiscalCode(Validator $validator, ?Product $existingProduct): void
    {
        $productType = $this->input('product.product_type', $existingProduct?->product_type);

        if ($productType !== Constant::PRODUCT_TYPE_SINGLE) {
            return;
        }

        $fiscalCode = $this->has('product.fiscal_code')
            ? $this->input('product.fiscal_code')
            : $existingProduct?->fiscal_code?->value;

        if ($fiscalCode === null || $fiscalCode === '') {
            $validator->errors()->add('product.fiscal_code', 'The fiscal_code field is required for single products.');

            return;
        }

        $fiscalCodeEnum = FiscalCode::tryFrom($fiscalCode);

        if (! $fiscalCodeEnum) {
            return; // Ya reportado por la regla Enum de rules().
        }

        $commerceId = $this->input('product.commerce_id', $existingProduct?->commerce_id);
        $commerce = $commerceId ? Commerce::find($commerceId) : null;

        if ($commerce && ! app(FiscalCodeResolver::class)->isAllowed($commerce, $fiscalCodeEnum)) {
            $validator->errors()->add(
                'product.fiscal_code',
                'This fiscal_code is not allowed for the commerce establishment type.'
            );
        }
    }

    /**
     * SCRUM-370: solo se exige cuando package_items viene en el payload —
     * ausente significa "no tocar la composición", y la categoría ya
     * derivada en BD sigue siendo válida sin necesidad de recalcularla.
     */
    private function validatePackageCategoryIsDerivable(Validator $validator, ?Product $existingPackage): void
    {
        if (! $this->has('package_items')) {
            return;
        }

        $productType = $this->input('product.product_type', $existingPackage?->product_type);

        if ($productType !== Constant::PRODUCT_TYPE_PACKAGE) {
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
     * Ajuste funcional 2026-08-03: un pack se ofrece en una sola sede.
     * Valor efectivo en ambos lados — product_type y las sedes pueden venir
     * de BD si el payload no los reenvía (ej. una edición que solo agrega
     * una sede a un pack ya armado, sin tocar product.product_type).
     */
    private function validateSingleBranchPolicyForPackages(Validator $validator, ?Product $existingPackage): void
    {
        $productType = $this->input('product.product_type', $existingPackage?->product_type);

        if ($productType !== Constant::PRODUCT_TYPE_PACKAGE) {
            return;
        }

        $branchesInput = $this->has('commerce_branches')
            ? collect($this->input('commerce_branches', []))
            : ($existingPackage?->commerceBranches->map(fn ($branch) => [
                'commerce_branch_id' => $branch->id,
            ]) ?? collect());

        $branchIds = $branchesInput->pluck('commerce_branch_id')->filter()->map(fn ($id) => (int) $id)->values();

        if (app(PackageCompositionValidator::class)->exceedsSingleBranchPolicy($branchIds)) {
            $validator->errors()->add(
                'commerce_branches',
                'A package can only be offered in a single branch. Use "Duplicate" to offer it in another branch.'
            );
        }
    }

    /**
     * SCRUM-361 (Tarea 4, SCRUM-323): misma regla que StoreProductRequest,
     * con valor efectivo (payload ?? BD) para sedes y componentes — una
     * edición que solo toca package_items, sin reenviar commerce_branches,
     * debe seguir validando contra las sedes ya asignadas al pack.
     */
    private function validatePackageComposition(Validator $validator, ?Product $existingPackage, ?int $currentPackageId): void
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
                    "The product '{$product->title}' cannot be added to a package while its fiscal classification is pending review."
                );

                continue;
            }

            if ($product->product_type !== Constant::PRODUCT_TYPE_SINGLE) {
                $validator->errors()->add(
                    "package_items.{$index}.product_id",
                    "The product '{$product->title}' must be of type 'single' to be included in a package."
                );

                continue;
            }

            $packageItems->push(['index' => $index, 'product' => $product, 'quantity' => (int) $item['quantity']]);
        }

        $productType = $this->input('product.product_type', $existingPackage?->product_type);

        if ($packageItems->isEmpty() || $productType !== Constant::PRODUCT_TYPE_PACKAGE) {
            return;
        }

        // Valor efectivo de las sedes: lo que venga en el payload si la clave
        // está presente, o si no, las sedes ya asignadas al pack en BD.
        $branchesInput = $this->has('commerce_branches')
            ? collect($this->input('commerce_branches', []))
            : ($existingPackage?->commerceBranches->map(fn ($branch) => [
                'commerce_branch_id' => $branch->id,
                'quantity_available' => $branch->pivot->quantity_available,
            ]) ?? collect());

        $branchIds = $branchesInput->pluck('commerce_branch_id')->filter()->map(fn ($id) => (int) $id)->values();

        $compositionValidator = app(PackageCompositionValidator::class);

        foreach ($compositionValidator->componentsMissingBranchStock($packageItems, $branchIds) as $missing) {
            $branchName = CommerceBranch::find($missing['branchId'])?->name ?? "branch #{$missing['branchId']}";

            $validator->errors()->add(
                "package_items.{$missing['index']}.product_id",
                "The product '{$missing['product']->title}' has no stock assigned in branch '{$branchName}'."
            );
        }

        $requestedByBranch = $branchesInput->mapWithKeys(
            fn ($branch) => [(int) $branch['commerce_branch_id'] => (int) ($branch['quantity_available'] ?? 0)]
        );

        // Solo apunta a un índice del payload si commerce_branches vino explícito;
        // si el valor es efectivo (heredado de BD), no hay campo del request al
        // que atribuir el error, así que se registra sobre package_items.
        $branchIndexById = $this->has('commerce_branches')
            ? collect($this->input('commerce_branches', []))->mapWithKeys(fn ($branch, $index) => [(int) $branch['commerce_branch_id'] => $index])
            : collect();

        foreach ($compositionValidator->maxPacksByBranch($packageItems, $branchIds, $currentPackageId) as $branchId => $maxPacks) {
            $requested = $requestedByBranch->get($branchId, 0);

            if ($requested <= $maxPacks) {
                continue;
            }

            $branchName = CommerceBranch::find($branchId)?->name ?? "branch #{$branchId}";
            $errorKey = $branchIndexById->has($branchId)
                ? "commerce_branches.{$branchIndexById->get($branchId)}.quantity_available"
                : 'package_items';

            $validator->errors()->add(
                $errorKey,
                "The requested quantity_available ({$requested}) exceeds the maximum packs available in branch '{$branchName}' given current stock (max: {$maxPacks})."
            );
        }

        $submittedOriginalPrice = $this->input('product.original_price', $existingPackage?->original_price);
        $this->validatePackagePriceCeiling($validator, $packageItems, $submittedOriginalPrice);
    }

    /**
     * Ticket derivado de SCRUM-361/323 (2026-08-04): mismo techo que
     * StoreProductRequest, con valor efectivo para original_price — una
     * edición que solo toca package_items, sin reenviar product.original_price,
     * debe seguir validando contra el precio ya guardado.
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
                "The package price must equal the sum of its components' current prices (expected: {$expected})."
            );
        }
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

    /**
     * No se puede publicar (is_published=true) una sede sin inventario
     * cargado. Para packs, validatePackageComposition() ya rechaza cualquier
     * quantity_available que exceda la capacidad real de los componentes,
     * así que esta misma condición cubre "sin componentes suficientes"
     * (SCRUM-361, Tarea 5.3 — levanta el guard "packages cannot be
     * published yet" de la Fase 1).
     */
    private function validateCommerceBranches(Validator $validator, ?Product $existingProduct): void
    {
        $fiscalCode = $this->has('product.fiscal_code')
            ? $this->input('product.fiscal_code')
            : $existingProduct?->fiscal_code?->value;

        foreach ($this->input('commerce_branches', []) as $index => $branch) {
            if (! ($branch['is_published'] ?? false)) {
                continue;
            }

            // SCRUM-362: un producto sin clasificar no se publica — solo
            // aplica a 'single', un pack nunca tiene fiscal_code propio.
            if ($fiscalCode === FiscalCode::PendingReview->value) {
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
