<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\Constant;
use App\Enums\FiscalCode;
use App\Exceptions\ProductUpdateConfirmationRequiredException;
use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCommerceBranch;
use App\Models\ProductPhoto;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service class for Product business logic.
 */
class ProductService
{
    /**
     * @var DocumentUploadService Document upload service instance.
     */
    protected $documentUploadService;

    /**
     * Constructor
     */
    public function __construct(
        private readonly BranchAvailabilityCalculator $branchAvailabilityCalculator,
        private readonly PackageCommitmentSyncService $packageCommitmentSyncService,
        private readonly PackageAvailabilityCalculator $packageAvailabilityCalculator
    ) {
        $this->documentUploadService = new DocumentUploadService;
    }

    /**
     * Get paginated list of products with optional filters.
     */
    public function index(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Product::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['title'])) {
            $query->where('title', 'like', '%'.$filters['title'].'%');
        }
        if (isset($filters['description'])) {
            $query->where('description', 'like', '%'.$filters['description'].'%');
        }
        if (isset($filters['commerce_id'])) {
            $query->where('commerce_id', $filters['commerce_id']);
        }
        if (isset($filters['product_category_id'])) {
            $query->where('product_category_id', $filters['product_category_id']);
        }

        $allowedSorts = ['title', 'status', 'created_at', 'updated_at'];
        $sortByCandidate = $filters['sort_by'] ?? 'title';
        $sortBy = in_array($sortByCandidate, $allowedSorts, true) ? $sortByCandidate : 'title';
        $sortDir = ($filters['sort_dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($sortBy, $sortDir)->paginate($perPage);
    }

    /**
     * SCRUM-362 (CA-09): reporte interno de productos sin clasificar.
     * Eager-load de `commerce` para que el listado muestre a qué comercio
     * pertenece cada uno sin una consulta por fila.
     */
    public function paginatePendingFiscalClassification(?int $commerceId, int $perPage = 15): LengthAwarePaginator
    {
        $query = Product::query()
            ->with('commerce')
            ->where('fiscal_code', FiscalCode::PendingReview);

        if ($commerceId !== null) {
            $query->where('commerce_id', $commerceId);
        }

        return $query->orderBy('created_at')->paginate($perPage);
    }

    /**
     * Store a new product.
     *
     * @throws Exception
     */
    public function store(array $data): Product
    {
        try {

            return DB::transaction(function () use ($data) {

                $product = Product::create($this->applyFiscalDerivation($data['product']));

                // Commerce Branches
                $this->storeCommerceBranches($product, $data['commerce_branches'] ?? []);

                // Photos
                $this->storePhotos($product->id, $data['photos'] ?? []);

                return $product->load(['photos', 'commerceBranches']);
            });

        } catch (Exception $e) {
            Log::error('Error creating Product', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * SCRUM-362: deriva vat_rate/applies_inc/inc_rate de fiscal_code en un
     * solo punto — el aliado nunca digita un porcentaje, y ningún caller de
     * store()/update() debe calcular esto por su cuenta. Sin fiscal_code en
     * el payload (packs, o una edición parcial que no lo toca), no hay nada
     * que derivar.
     */
    private function applyFiscalDerivation(array $productData): array
    {
        if (! array_key_exists('fiscal_code', $productData) || $productData['fiscal_code'] === null) {
            return $productData;
        }

        $fiscalCode = $productData['fiscal_code'] instanceof FiscalCode
            ? $productData['fiscal_code']
            : FiscalCode::from($productData['fiscal_code']);

        $productData['vat_rate'] = $fiscalCode->vatRate();
        $productData['applies_inc'] = $fiscalCode->appliesInc();
        $productData['inc_rate'] = $fiscalCode->incRate();

        return $productData;
    }

    /**
     * SCRUM-370: la categoría del pack se deriva del componente de mayor
     * valor prorrateado (precio vigente × cantidad), no se le pregunta al
     * aliado. Desempate determinista por menor product_category_id para que
     * el resultado no dependa del orden de iteración.
     *
     * @param  array<int, array{product_id: int, quantity: int}>  $packageItems
     */
    private function deriveCategoryFromComponents(array $packageItems): ?int
    {
        $components = collect($packageItems)
            ->map(fn ($item) => [
                'product' => isset($item['product_id']) ? Product::find($item['product_id']) : null,
                'quantity' => (int) ($item['quantity'] ?? 0),
            ])
            ->filter(fn (array $component) => $component['product'] !== null);

        if ($components->isEmpty()) {
            return null;
        }

        $best = $components->sortBy(fn (array $component) => [
            -($component['product']->currentSalePrice() * $component['quantity']),
            $component['product']->product_category_id,
        ])->first();

        return $best['product']->product_category_id;
    }

    /**
     * Store product photos.
     */
    protected function storePhotos(int $productId, array $photos): void
    {
        $product_photo = [];

        foreach ($photos as $photo) {

            $presignedUrlData = $this->documentUploadService->generatePresignedUrl(
                $photo['file_name'],
                $photo['mime_type'],
                $productId,
                'product_photos'
            );

            $product_photo[] = [
                'product_id' => $productId,
                'file_path' => $presignedUrlData['path'],
                'upload_token' => $presignedUrlData['upload_token'],
                'presigned_url' => $presignedUrlData['presigned_url'],
                'mime_type' => $photo['mime_type'],
                'uploaded_at' => now(),
                'expires_at' => now()->addHour(),
                'uploaded_by_id' => auth()->guard()->id(),
                'failed_attempts' => 0,
            ];

        }

        ProductPhoto::insert($product_photo);
    }

    /**
     * Store commerce branches for a product, with their per-branch inventory
     * and publication state (SCRUM-277 Fase 1).
     *
     * $branches === null significa que la clave vino ausente del payload:
     * no se toca la relación existente (comportamiento de update parcial).
     * $branches === [] significa que vino presente pero vacía a propósito:
     * sí se limpia la relación (comportamiento de "quitar todas las sedes").
     * Cuando la clave sí viene, representa el estado deseado COMPLETO de la
     * asignación — no un delta — así que sync() puede reemplazarla sin más.
     *
     * Un detach()+attach() ciego destruiría en cada edición el inventario y
     * la publicación de las sedes que no cambian — el mismo patrón que ya
     * causó SCRUM-303/306. sync() con payload de pivote resuelve exactamente
     * eso: solo suelta las sedes ausentes y solo agrega/actualiza las que
     * vienen en el payload, dejando registrado únicamente lo que el cliente
     * pidió explícitamente.
     *
     * Para packs, además, editar la asignación de una sede limpia su marca
     * de ajuste automático en esa sede (SCRUM-361, Tarea 3.8): el aliado
     * acaba de fijar la cantidad a mano, así que cualquier aviso sobre el
     * valor anterior queda obsoleto. sync()->updateExistingPivot() solo
     * toca las columnas que se le pasan, así que hay que incluirlas
     * explícitamente aquí o quedarían con el valor viejo.
     *
     * @param  array<int, array{commerce_branch_id: int, quantity_available: int, is_published?: bool}>|null  $branches
     */
    protected function storeCommerceBranches(Product $product, ?array $branches): void
    {
        if ($branches === null) {
            return;
        }

        $isPackage = $product->product_type === Constant::PRODUCT_TYPE_PACKAGE;

        $syncPayload = collect($branches)->mapWithKeys(fn (array $branch) => [
            $branch['commerce_branch_id'] => array_merge([
                'quantity_available' => $branch['quantity_available'],
                'is_published' => $branch['is_published'] ?? false,
            ], $isPackage ? ['auto_adjusted_at' => null, 'auto_adjusted_from' => null] : []),
        ])->all();

        $product->commerceBranches()->sync($syncPayload);
    }

    /**
     * Publish or unpublish a product in a single branch, without touching
     * its assignment to any other branch (SCRUM-277 Fase 1, Tarea 3.2).
     *
     * @throws ModelNotFoundException si el producto no tiene esa sede asignada
     */
    public function updateBranchPublication(int $productId, int $branchId, bool $isPublished): Product
    {
        $pivot = ProductCommerceBranch::query()
            ->where('product_id', $productId)
            ->where('commerce_branch_id', $branchId)
            ->firstOrFail();

        $pivot->is_published = $isPublished;
        $pivot->save();

        return Product::with(['category', 'commerce', 'commerceBranches'])->findOrFail($productId);
    }

    /**
     * Descarta el aviso de ajuste automático de un pack en una sede, sin
     * tocar su cantidad comprometida (SCRUM-361, Tarea 3.8 — segundo camino
     * de limpieza, para el aliado que ya está conforme con la cantidad
     * nueva y solo quiere quitar el aviso de la tarjeta).
     *
     * @throws ModelNotFoundException si el producto no tiene esa sede asignada
     */
    public function dismissAutoAdjustment(int $productId, int $branchId): Product
    {
        $pivot = ProductCommerceBranch::query()
            ->where('product_id', $productId)
            ->where('commerce_branch_id', $branchId)
            ->firstOrFail();

        $this->packageCommitmentSyncService->clearAutomaticAdjustmentMark($pivot);

        return Product::with(['category', 'commerce', 'commerceBranches'])->findOrFail($productId);
    }

    /**
     * Show a product by ID.
     *
     * @throws ModelNotFoundException
     */
    public function show(int $id): Product
    {
        return Product::with(['category', 'commerce', 'commerceBranches'])->findOrFail($id);
    }

    /**
     * Mostrar un producto para consumo público (app cliente).
     * A diferencia de show(), solo expone productos activos: un producto
     * inactivo/borrador no debe ser visible por id aunque se conozca el id.
     */
    public function showPublic(int $id): Product
    {
        // SCRUM-277 Fase 1: el detalle público necesita el stock real por sede
        // (ProductResource.commerce_branches[]) — sin esto, la página de detalle
        // y el carrito de la app móvil leerían quantity_available a nivel de
        // producto, vestigial para single (siempre 0), y limitarían la compra
        // a 0/1 unidades sin importar el stock real de la sede.
        // Solo sedes publicadas: un cliente nunca debe ver el inventario de una
        // sede que el aliado no ha hecho visible.
        return Product::with([
            'category',
            'commerce',
            'photos',
            'commerceBranches' => fn ($query) => $query->wherePivot('is_published', true),
        ])
            ->where('status', Constant::STATUS_ACTIVE)
            ->findOrFail($id);
    }

    /**
     * Update a product by ID.
     *
     * SCRUM-362/361 (unificación — ver plan
     * unificacionConfirmacion409Fiscal361): dos motivos independientes
     * pueden exigir confirmación en el mismo submit — reclasificar a
     * otro_verificar despublica sedes/packs (SCRUM-362, D9) y bajar el
     * stock de un componente puede sobre-comprometer packs (SCRUM-361,
     * Tarea 3). Ambos se DETECTAN antes de decidir si se exige
     * confirmación, y solo entonces se lanza
     * ProductUpdateConfirmationRequiredException con los que apliquen —
     * así el aliado confirma una sola vez, en vez de que el primero en
     * dispararse esconda al segundo.
     *
     * Asimetría entre ambas detecciones, a propósito: el impacto fiscal se
     * calcula ANTES de escribir (no depende de nada que esta misma edición
     * vaya a cambiar), pero el impacto de stock necesita el stock YA
     * actualizado por storeCommerceBranches() para saber cuántos packs
     * soporta de verdad — por eso se calcula DESPUÉS. Las dos cascadas de
     * aplicación (applyFiscalUnpublishCascade / applyComponentEditAdjustments)
     * también corren DESPUÉS de storeCommerceBranches(): el formulario real
     * reenvía siempre el estado completo de sedes, y ese sync() pisaría
     * cualquier cascada que corriera antes.
     *
     * @throws Exception
     * @throws ProductUpdateConfirmationRequiredException
     */
    public function update(int $id, array $data, bool $confirmChanges = false): Product
    {
        try {

            return DB::transaction(function () use ($data, $id, $confirmChanges) {

                $product = Product::findOrFail($id);

                $isReclassifyingToPendingReview = array_key_exists('fiscal_code', $data['product'])
                    && $data['product']['fiscal_code'] === FiscalCode::PendingReview->value
                    && $product->fiscal_code !== FiscalCode::PendingReview;

                $fiscalImpact = $isReclassifyingToPendingReview
                    ? $this->resolveFiscalReclassificationImpact($product)
                    : null;

                $isSingleWithBranchChanges = $product->product_type === Constant::PRODUCT_TYPE_SINGLE
                    && array_key_exists('commerce_branches', $data)
                    && $data['commerce_branches'] !== null;

                $previousBranchQuantities = $isSingleWithBranchChanges
                    ? $product->commerceBranches()->get()->mapWithKeys(
                        fn ($branch) => [(int) $branch->id => (int) $branch->pivot->quantity_available]
                    )
                    : collect();

                $product->update($this->applyFiscalDerivation($data['product']));

                // Commerce Branches: null = clave ausente del payload, no tocar la
                // relacion existente. [] = clave presente y vacia, limpiar a proposito.
                $this->storeCommerceBranches($product, $data['commerce_branches'] ?? null);

                // Photos
                $this->storePhotos($product->id, $data['photos'] ?? []);

                $stockImpact = collect();

                if ($isSingleWithBranchChanges) {
                    $touchedBranchIds = $this->branchesWithReducedOrRemovedStock($previousBranchQuantities, $data['commerce_branches']);

                    if ($touchedBranchIds->isNotEmpty()) {
                        $stockImpact = $this->packageCommitmentSyncService->resolveComponentEditImpact($product, $touchedBranchIds);
                    }
                }

                if (($fiscalImpact || $stockImpact->isNotEmpty()) && ! $confirmChanges) {
                    throw new ProductUpdateConfirmationRequiredException(
                        $fiscalImpact ? [
                            'affected_branches' => $fiscalImpact['branches']->map(fn ($branch) => [
                                'commerce_branch_id' => $branch->id,
                                'commerce_branch_name' => $branch->name,
                            ])->all(),
                            'affected_packages' => $fiscalImpact['packages']->map(fn (Product $package) => [
                                'package_id' => $package->id,
                                'package_title' => $package->title,
                            ])->values()->all(),
                        ] : null,
                        $stockImpact->isNotEmpty() ? [
                            'affected_packages' => $this->packageCommitmentSyncService->toStockPayload($stockImpact),
                        ] : null
                    );
                }

                if ($fiscalImpact) {
                    $this->applyFiscalUnpublishCascade($fiscalImpact);
                }

                if ($stockImpact->isNotEmpty()) {
                    $this->packageCommitmentSyncService->applyComponentEditAdjustments($stockImpact);
                }

                return $product->load(['photos', 'commerceBranches']);

            });

        } catch (Exception $e) {
            Log::error('Error updating Product', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Sedes donde el stock de un componente bajó o se le quitó la
     * asignación por completo — las únicas que pueden dejar un pack
     * sobre-comprometido (subir stock nunca reduce lo que un pack puede
     * ofrecer).
     *
     * @param  \Illuminate\Support\Collection<int, int>  $previousQuantities  Cantidad anterior por commerce_branch_id.
     * @param  array<int, array{commerce_branch_id: int, quantity_available: int}>  $newBranches
     * @return \Illuminate\Support\Collection<int, int>
     */
    private function branchesWithReducedOrRemovedStock($previousQuantities, array $newBranches)
    {
        $newQuantities = collect($newBranches)->mapWithKeys(
            fn (array $branch) => [(int) $branch['commerce_branch_id'] => (int) $branch['quantity_available']]
        );

        return $previousQuantities->keys()
            ->merge($newQuantities->keys())
            ->unique()
            ->filter(function (int $branchId) use ($previousQuantities, $newQuantities) {
                $old = $previousQuantities->get($branchId);

                if ($old === null) {
                    return false; // sede nueva: no había compromiso previo que proteger.
                }

                $new = $newQuantities->get($branchId); // null si la sede se removió del payload.

                return $new === null || $new < $old;
            })
            ->values();
    }

    /**
     * Update only product status by ID.
     *
     * @throws Exception
     */
    public function patchStatus(int $id, string $status): Product
    {
        try {
            $product = Product::findOrFail($id);
            $product->status = $status;
            $product->save();

            return $product;
        } catch (Exception $e) {
            Log::error('Error patching Product status', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete a product by ID.
     *
     * @throws Exception
     */
    public function destroy(int $id): void
    {
        try {
            $product = Product::findOrFail($id);
            $product->delete();
        } catch (Exception $e) {
            Log::error('Error deleting Product', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get products by commerce ID.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByCommerce(int $commerce_id)
    {
        try {
            // commerceBranches: sin esto, el listado del proveedor no traía el
            // inventario/publicación por sede (SCRUM-277) ni la sede de cada
            // producto individual — necesario para filtrar candidatos a pack
            // por sede (SCRUM-323).
            $products = Product::with(['photos', 'packageItems', 'packageItems.photos', 'package', 'commerceBranches'])
                ->where('commerce_id', $commerce_id)
                ->get();

            $this->attachAvailableForPackaging($products);

            return $products;
        } catch (ModelNotFoundException $e) {
            Log::error('Error fetching products by commerce ID', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Adjunta, al pivote de cada sede de un producto individual, cuánto de
     * su stock queda libre para comprometer en packs en esa sede — lo que
     * la interfaz de armado de packs necesita para filtrar candidatos por
     * sede (SCRUM-361, Tarea 6.2). Se agrupa por sede y se resuelve con
     * PackageAvailabilityCalculator::availableForPackagingMany() (ya en
     * lote) para no emitir una consulta por producto al listar el catálogo
     * completo de un comercio.
     *
     * @param  \Illuminate\Database\Eloquent\Collection<int, Product>  $products
     */
    private function attachAvailableForPackaging($products): void
    {
        $singleProducts = $products->filter(
            fn (Product $product) => $product->product_type === Constant::PRODUCT_TYPE_SINGLE
        );

        if ($singleProducts->isEmpty()) {
            return;
        }

        $branchIds = $singleProducts
            ->flatMap(fn (Product $product) => $product->commerceBranches->pluck('id'))
            ->unique();

        foreach ($branchIds as $branchId) {
            $componentsAtBranch = $singleProducts->filter(
                fn (Product $product) => $product->commerceBranches->contains('id', $branchId)
            );

            $availability = $this->packageAvailabilityCalculator->availableForPackagingMany($componentsAtBranch, $branchId);

            foreach ($componentsAtBranch as $product) {
                $branch = $product->commerceBranches->firstWhere('id', $branchId);
                $branch->pivot->setAttribute('available_for_packaging', $availability->get($product->id, 0));
            }
        }
    }

    /**
     * Get products by commerce branch ID.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByCommerceBranch(int $branch_id)
    {
        $products = Product::with(['photos', 'packageItems', 'packageItems.photos', 'package'])
            ->whereHas('commerce.commerceBranches', function ($query) use ($branch_id) {
                $query->where('id', $branch_id);
            })->get();

        if ($products->isEmpty()) {
            throw new ModelNotFoundException('No products found for the given commerce branch.');
        }

        return $products;
    }

    /**
     * Get product package items by product ID.
     *
     * @return Product
     *
     * @throws ModelNotFoundException
     */
    public function getProductPackage(int $product_package_id)
    {
        try {
            $product = Product::where(['id' => $product_package_id, 'product_type' => Constant::PRODUCT_TYPE_PACKAGE])->firstOrFail();

            return $product->load(['packageItems', 'packageItems.photos']);
        } catch (ModelNotFoundException $e) {
            Log::error('Error fetching package items for product ID: '.$product_package_id, ['error' => $e->getMessage()]);
            throw new ModelNotFoundException('Product Package not found with the specified ID.');
        }
    }

    /**
     * Store product package items.
     *
     * @throws Exception
     */
    public function storePackageItems(array $data): Product
    {
        try {

            return DB::transaction(function () use ($data) {

                $data['product']['product_type'] = Constant::PRODUCT_TYPE_PACKAGE;

                // SCRUM-370: la categoría no se le pregunta al pack, se deriva.
                if (isset($data['package_items']) && is_array($data['package_items'])) {
                    $derivedCategoryId = $this->deriveCategoryFromComponents($data['package_items']);

                    if ($derivedCategoryId !== null) {
                        $data['product']['product_category_id'] = $derivedCategoryId;
                    }
                }

                $productPackage = $this->store($data);

                $productPackage->packageItems()->detach();

                if (isset($data['package_items']) && is_array($data['package_items'])) {
                    $itemsWithQuantity = collect($data['package_items'])->mapWithKeys(function ($item) {
                        return [$item['product_id'] => ['quantity' => $item['quantity']]];
                    })->toArray();
                    $productPackage->packageItems()->attach($itemsWithQuantity);
                }

                return $productPackage->load(['packageItems', 'packageItems.photos']);
            });

        } catch (Exception $e) {
            Log::error('Error storing ProductPackageItems', ['error' => $e->getMessage().' on line '.$e->getLine()]);
            throw $e;
        }
    }

    /**
     * Update product package items.
     *
     * @throws Exception
     */
    public function updatePackageItems(int $product_package_id, array $items): Product
    {
        try {
            $items['product']['product_type'] = Constant::PRODUCT_TYPE_PACKAGE;

            // SCRUM-370: recalcular la categoría derivada solo cuando la
            // composición cambia — ausente significa "no tocar" (D9/3.9).
            if (array_key_exists('package_items', $items)) {
                $derivedCategoryId = $this->deriveCategoryFromComponents($items['package_items'] ?? []);

                if ($derivedCategoryId !== null) {
                    $items['product']['product_category_id'] = $derivedCategoryId;
                }
            }

            $productPackage = $this->update($product_package_id, $items);

            // package_items ausente del payload: no tocar los items existentes
            // (confirmado como bug real: editar un pack sin enviar esta clave
            // borraba todos sus productos). Presente y vacío sí limpia a propósito.
            if (array_key_exists('package_items', $items)) {
                $productPackage->packageItems()->detach();

                if (! empty($items['package_items'])) {
                    $itemsWithQuantity = collect($items['package_items'])->mapWithKeys(function ($item) {
                        return [$item['product_id'] => ['quantity' => $item['quantity']]];
                    })->toArray();
                    $productPackage->packageItems()->attach($itemsWithQuantity);
                }
            }

            return $productPackage->load(['packageItems', 'packageItems.photos']);

        } catch (Exception $e) {
            Log::error('Error updating ProductPackageItems', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Dismiss confirmed stock for an order, reducing the stock committed by
     * each product in the order once it transitions out of the "active"
     * (reserved) states.
     *
     * Se bloquea cada fila afectada con lockForUpdate dentro de una
     * transacción propia: dos confirmaciones concurrentes sobre el mismo
     * producto/sede (dos órdenes distintas) deben serializarse aquí, o de lo
     * contrario ambas leerían el mismo valor antes de restar y se perdería
     * una de las dos actualizaciones (sobreventa).
     *
     * SCRUM-277/361: el stock de ambos tipos de producto vive por sede en
     * product_commerce_branch. Vender un pack descuenta dos cosas en la sede
     * de la orden: su propio compromiso (igual que un individual) y, además,
     * el inventario de cada uno de sus componentes — esas unidades salieron
     * físicamente de la tienda. No confundir con el compromiso (planificación,
     * no mueve inventario): la venta sí lo mueve. Antes de esta fase, la rama
     * de packs saltaba el descuento de componentes por completo (bug latente,
     * inactivo mientras los packs no eran vendibles).
     *
     * SCRUM-361, Tarea 3.6-3.9 (disparador por compra): cada vez que el
     * stock de un componente (single) baja aquí — por su propia venta o por
     * ser componente de un pack vendido — se sincronizan en la misma
     * transacción los packs de esa sede que queden sobre-comprometidos. Sin
     * aliado a quien preguntar, el ajuste es silencioso y queda marcado
     * (PackageCommitmentSyncService::syncAfterPurchase). Vender el propio
     * pack no dispara sync sobre sí mismo: un pack no puede ser componente
     * de otro pack (SCRUM-323 exige componentes "single").
     */
    public function dismissProductConfirmedStock(Order $order): void
    {
        try {
            DB::transaction(function () use ($order) {
                foreach ($order->items as $item) {
                    $product = Product::query()->lockForUpdate()->find($item->product_id);

                    if (! $product) {
                        continue;
                    }

                    $this->dismissBranchConfirmedStock($product->id, $order->commerce_branch_id, $item->quantity);

                    if ($product->product_type === Constant::PRODUCT_TYPE_PACKAGE) {
                        $this->dismissPackageComponentsStock($product, $order->commerce_branch_id, $item->quantity);
                    } else {
                        $this->packageCommitmentSyncService->syncAfterPurchase($product, $order->commerce_branch_id);
                    }
                }
            });
        } catch (Exception $e) {
            Log::error('Error dismissing confirmed stock for order ID: '.$order->id, ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Descuenta, bajo bloqueo, el stock/compromiso de un producto (single o
     * package) en la sede donde se confirmó la orden. Si la venta agota la
     * fila (queda en 0), se despublica automáticamente — misma regla que ya
     * impide publicar sin inventario/compromiso; sin este ajuste, un
     * producto vendido hasta agotar su stock seguía figurando "publicado"
     * en el panel del aliado aunque ya no fuera comprable en la práctica
     * (la discovery lo oculta igual por quantity_available=0, pero el panel
     * no reflejaba la realidad).
     */
    private function dismissBranchConfirmedStock(int $productId, int $commerceBranchId, int $quantity): void
    {
        $pivot = ProductCommerceBranch::query()
            ->lockForUpdate()
            ->where('product_id', $productId)
            ->where('commerce_branch_id', $commerceBranchId)
            ->first();

        if (! $pivot) {
            return;
        }

        $pivot->quantity_available = max(0, $pivot->quantity_available - $quantity);

        if ($pivot->quantity_available === 0) {
            $pivot->is_published = false;
        }

        $pivot->save();
    }

    /**
     * Descuenta, en la misma sede de la orden, el inventario de cada
     * componente del pack vendido (SCRUM-361, Tarea 5.4) y sincroniza los
     * demás packs de esa sede que compartan ese componente (Tarea 3.6) —
     * el propio pack vendido no se re-sincroniza a sí mismo aquí: su
     * compromiso ya se descontó en lockstep, exactamente por la cantidad
     * vendida, así que no debería aparecer sobre-comprometido (Tarea 5.4b).
     */
    private function dismissPackageComponentsStock(Product $package, int $commerceBranchId, int $packagesSold): void
    {
        $package->loadMissing('packageItems');

        foreach ($package->packageItems as $component) {
            $this->dismissBranchConfirmedStock(
                $component->id,
                $commerceBranchId,
                (int) $component->pivot->quantity * $packagesSold
            );

            $this->packageCommitmentSyncService->syncAfterPurchase($component, $commerceBranchId);
        }
    }

    /**
     * Validar la disponibilidad de los productos en los items de la orden.
     *
     * SCRUM-277/361: ambos tipos de producto validan contra la sede donde se
     * está creando la orden — el pivote producto-sede es la única fuente de
     * verdad, tanto para stock físico (single) como para compromiso (package).
     */
    public function validateProductAvailability(array $items, int $commerceBranchId): bool
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);

            if (! $product) {
                return false;
            }

            if (! $this->isFiscallyPurchasable($product)) {
                return false;
            }

            $pivot = ProductCommerceBranch::query()
                ->where('product_id', $product->id)
                ->where('commerce_branch_id', $commerceBranchId)
                ->first();

            if (! $pivot || $this->branchAvailabilityCalculator->availableFor($pivot) < $item['quantity']) {
                return false;
            }
        }

        return true;
    }

    /**
     * SCRUM-362 (CR-01): un producto con fiscal_code = otro_verificar nunca
     * puede terminar en un carrito pagado, ni suelto ni como componente de
     * un pack. La composición ya bloquea agregar un componente así
     * (StoreProductRequest/UpdateProductRequest), pero se revalida aquí en
     * profundidad, en el punto real de venta — la única fuente de verdad que
     * importa cuando hay dinero de por medio.
     */
    private function isFiscallyPurchasable(Product $product): bool
    {
        if ($product->fiscal_code === FiscalCode::PendingReview) {
            return false;
        }

        if ($product->product_type === Constant::PRODUCT_TYPE_PACKAGE) {
            $product->loadMissing('packageItems');

            return $product->packageItems->every(
                fn (Product $component) => $component->fiscal_code !== FiscalCode::PendingReview
            );
        }

        return true;
    }

    /**
     * SCRUM-362 (D9): calcula el impacto de reclasificar a otro_verificar
     * (sedes publicadas del producto y packs que lo contienen y están
     * publicados). Detección pura — no aplica nada ni exige confirmación
     * aquí; el caller (ProductService::update()) combina este resultado con
     * el impacto de stock antes de decidir si exige confirmación (SCRUM-362/
     * 361, unificación). Debe ejecutarse ANTES de escribir nada, mientras
     * que aplicar el cambio espera a DESPUÉS de storeCommerceBranches() (ver
     * applyFiscalUnpublishCascade()). Sin impacto real, retorna null.
     *
     * @return array{product: Product, branches: \Illuminate\Support\Collection, packages: \Illuminate\Support\Collection}|null
     */
    private function resolveFiscalReclassificationImpact(Product $product): ?array
    {
        $publishedBranches = $product->commerceBranches()->wherePivot('is_published', true)->get();

        $affectedPackages = $product->package()->get()->filter(
            fn (Product $package) => $package->commerceBranches()->wherePivot('is_published', true)->exists()
        );

        if ($publishedBranches->isEmpty() && $affectedPackages->isEmpty()) {
            return null;
        }

        return ['product' => $product, 'branches' => $publishedBranches, 'packages' => $affectedPackages];
    }

    /**
     * SCRUM-362 (D9): aplica la despublicación en cascada calculada por
     * resolveFiscalReclassificationImpact(). Se llama DESPUÉS de
     * storeCommerceBranches() a propósito: el formulario real reenvía
     * siempre el estado completo de sedes, incluidas las que ya estaban
     * publicadas y el aliado no tocó, y ese sync() pisaría esta
     * despublicación si corriera antes — aquí siempre gana la última
     * escritura.
     *
     * @param  array{product: Product, branches: \Illuminate\Support\Collection, packages: \Illuminate\Support\Collection}  $impact
     */
    private function applyFiscalUnpublishCascade(array $impact): void
    {
        foreach ($impact['branches'] as $branch) {
            $impact['product']->commerceBranches()->updateExistingPivot($branch->id, ['is_published' => false]);
        }

        foreach ($impact['packages'] as $package) {
            foreach ($package->commerceBranches()->wherePivot('is_published', true)->get() as $branch) {
                $package->commerceBranches()->updateExistingPivot($branch->id, ['is_published' => false]);
            }
        }
    }

    /**
     * Validate store/update request for Product, checking user permissions and ownership of related commerce branches and commerce.
     * This method is used in both store and update requests to ensure that the user has the necessary permissions and ownership to create or update a product associated with specific commerce branches and commerce.
     */
    public function validateStoreRequest($user, $data): bool
    {
        try {
            if (! $user) {
                return false;
            }

            // Permitir si tiene al menos uno de los permisos
            if (! ($user->can('provider.products.update') || $user->can('provider.products.create'))) {
                return false;
            }

            if ($user->hasAnyRole(['superadmin', 'admin'])) {
                return true;
            }

            // commerce_branches es opcional (sometimes) en update: si no viene en el
            // payload, no es evidencia de nada y no debe bloquear la autorización — solo
            // se valida ownership de las sucursales que el cliente sí esté enviando.
            $branchIds = collect($data['commerce_branches'] ?? [])->pluck('commerce_branch_id')->all();

            if (! empty($branchIds)) {
                $ownedBranchesCount = CommerceBranch::query()
                    ->whereIn('id', $branchIds)
                    ->whereHas('commerce', function ($query) use ($user) {
                        $query->where('owner_user_id', $user->id);
                    })
                    ->count();

                if ($ownedBranchesCount !== count(array_unique($branchIds))) {
                    return false;
                }
            }

            return Commerce::query()
                ->where('id', $data['product']['commerce_id'] ?? null)
                ->where('owner_user_id', $user->id)
                ->exists();

        } catch (\Throwable $th) {
            Log::error('Error validating store request for Product', ['error' => $th->getMessage()]);

            return false;
        }
    }
}
