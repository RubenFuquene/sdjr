<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\ProductCategory;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Log;

/**
 * Service class for ProductCategory business logic.
 */
class ProductCategoryService
{
    /**
     * Get paginated list of product categories with optional filters.
     */
    public function index(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = ProductCategory::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['name'])) {
            $query->where('name', 'like', '%'.$filters['name'].'%');
        }
        if (isset($filters['description'])) {
            $query->where('description', 'like', '%'.$filters['description'].'%');
        }

        return $query->paginate($perPage);
    }

    /**
     * Store a new product category.
     *
     * @throws Exception
     */
    public function store(array $data): ProductCategory
    {
        try {
            return ProductCategory::create($data);
        } catch (Exception $e) {
            Log::error('Error creating ProductCategory', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Show a product category by ID.
     *
     * @throws ModelNotFoundException
     */
    public function show(int $id): ProductCategory
    {
        return ProductCategory::findOrFail($id);
    }

    /**
     * Update a product category by ID.
     *
     * @throws Exception
     */
    public function update(int $id, array $data): ProductCategory
    {
        try {
            $category = ProductCategory::findOrFail($id);
            $category->update($data);

            return $category;
        } catch (Exception $e) {
            Log::error('Error updating ProductCategory', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete a product category by ID.
     *
     * @throws Exception
     */
    public function destroy(int $id): void
    {
        try {
            $category = ProductCategory::findOrFail($id);
            $category->delete();
        } catch (Exception $e) {
            Log::error('Error deleting ProductCategory', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
