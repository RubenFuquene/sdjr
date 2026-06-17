<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\Order;
use App\Models\Product;
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
    public function __construct()
    {
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
     * Store a new product.
     *
     * @throws Exception
     */
    public function store(array $data): Product
    {
        try {

            return DB::transaction(function () use ($data) {

                $product = Product::create($data['product']);

                // Commerce Branches
                $this->storeCommerceBranches($product, $data['commerce_branch_ids'] ?? []);

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
     * Store commerce branches for a product.
     */
    protected function storeCommerceBranches(Product $product, array $branchIds): void
    {
        $product->commerceBranches()->detach();

        if (! empty($branchIds)) {
            $product->commerceBranches()->attach($branchIds);
        }
    }

    /**
     * Show a product by ID.
     *
     * @throws ModelNotFoundException
     */
    public function show(int $id): Product
    {
        return Product::findOrFail($id);
    }

    /**
     * Update a product by ID.
     *
     * @throws Exception
     */
    public function update(int $id, array $data): Product
    {
        try {

            return DB::transaction(function () use ($data, $id) {

                $product = Product::findOrFail($id);
                $product->update($data['product']);

                // Commerce Branches
                $this->storeCommerceBranches($product, $data['commerce_branch_ids'] ?? []);

                // Photos
                $this->storePhotos($product->id, $data['photos'] ?? []);

                return $product->load(['photos', 'commerceBranches']);

            });

        } catch (Exception $e) {
            Log::error('Error updating Product', ['error' => $e->getMessage()]);
            throw $e;
        }
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
            return Product::with(['photos', 'packageItems', 'packageItems.photos', 'package'])
                ->where('commerce_id', $commerce_id)
                ->get();
        } catch (ModelNotFoundException $e) {
            Log::error('Error fetching products by commerce ID', ['error' => $e->getMessage()]);
            throw $e;
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
            $productPackage = $this->update($product_package_id, $items);
            $productPackage->packageItems()->detach();
            if (! empty($items['package_items'])) {
                $itemsWithQuantity = collect($items['package_items'])->mapWithKeys(function ($item) {
                    return [$item['product_id'] => ['quantity' => $item['quantity']]];
                })->toArray();
                $productPackage->packageItems()->attach($itemsWithQuantity);
            }

            return $productPackage->load(['packageItems', 'packageItems.photos']);

        } catch (Exception $e) {
            Log::error('Error updating ProductPackageItems', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Dismiss confirmed stock for an order, reducing the total and available quantity of each product in the order.
     * This method is called when an order is confirmed, ensuring that the stock levels are updated accordingly.
     */
    public function dismissProductConfirmedStock(Order $order): void
    {
        try {
            foreach ($order->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {

                    ($product->quantity_total - $item->quantity) < 0 ? $product->quantity_total = 0 : $product->quantity_total -= $item->quantity;
                    $product->quantity_available = $product->quantity_total; // Asumiendo que quantity_available refleja el stock actual disponible
                    $product->save();
                }
            }

        } catch (Exception $e) {
            Log::error('Error dismissing confirmed stock for order ID: '.$order->id, ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Validar la disponibilidad de los productos en los items de la orden
     */
    public function validateProductAvailability(array $items): bool
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            if (! $product || $product->quantity_available < $item['quantity']) {
                return false;
            }
        }

        return true;
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

            $commerceBranch = CommerceBranch::query()
                ->whereIn('id', $data['commerce_branch_ids'] ?? [])
                ->whereHas('commerce', function ($query) use ($user) {
                    $query->where('owner_user_id', $user->id);
                })
                ->exists();

            if (! $commerceBranch) {
                return false;
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
