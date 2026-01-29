<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\Constant;
use App\Models\Product;
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
                $product->commerceBranches()->detach();
                if (isset($data['commerce_branch_ids']) && is_array($data['commerce_branch_ids'])) {
                    $product->commerceBranches()->attach($data['commerce_branch_ids']);
                }

                return $product;
            });

        } catch (Exception $e) {
            Log::error('Error creating Product', ['error' => $e->getMessage()]);
            throw $e;
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
            $product = Product::findOrFail($id);
            $product->update($data['product']);
            $product->commerceBranches()->detach();
            if (isset($data['commerce_branch_ids']) && is_array($data['commerce_branch_ids'])) {
                $product->commerceBranches()->attach($data['commerce_branch_ids']);
            }

            return $product;
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
        $products = Product::where('commerce_id', $commerce_id)->get();
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
        $products = Product::whereHas('commerce.commerceBranches', function ($query) use ($branch_id) {
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
