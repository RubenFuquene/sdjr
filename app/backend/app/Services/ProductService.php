<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\Constant;
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

        return $query->paginate($perPage);
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
                'uploaded_by_id' => auth()->id(),
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
        $products = Product::with('photos')->where('commerce_id', $commerce_id)->get();

        if ($products->isEmpty()) {
            throw new ModelNotFoundException('No products found for the specified commerce.');
        }

        return $products;
    }

    /**
     * Get products by commerce branch ID.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByCommerceBranch(int $branch_id)
    {
        $products = Product::with('photos')->whereHas('commerce.commerceBranches', function ($query) use ($branch_id) {
            $query->where('id', $branch_id);
        })->get();

        if ($products->isEmpty()) {
            throw new ModelNotFoundException('No products found for the given commerce branch.');
        }

        return $products;
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
                    $productPackage->packageItems()->attach($data['package_items']);
                }

                return $productPackage;
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
                $productPackage->packageItems()->attach($items['package_items']);
            }

            return $productPackage;

        } catch (Exception $e) {
            Log::error('Error updating ProductPackageItems', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
