<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryService
{
    /**
     * Get paginated categories.
     */
    public function getPaginated(int $perPage = 15, string $status = 'all'): LengthAwarePaginator
    {
        $query = Category::query();
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new category.
     */
    public function create(array $data): Category
    {
        return Category::create($data);
    }

    /**
     * Find a category by ID.
     */
    public function find(string $id): ?Category
    {
        return Category::find($id);
    }

    /**
     * Update a category.
     */
    public function update(Category $category, array $data): Category
    {
        $category->update($data);

        return $category;
    }

    /**
     * Delete a category.
     */
    public function delete(Category $category): bool
    {
        return $category->delete();
    }
}
