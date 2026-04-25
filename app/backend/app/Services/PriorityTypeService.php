<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PriorityType;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class PriorityTypeService
{
    /**
     * Get paginated priority types.
     */
    /**
     * Get paginated priority types with optional filters.
     */
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = PriorityType::query();

        if (! empty($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }
        if (! empty($filters['code'])) {
            $query->where('code', 'like', "%{$filters['code']}%");
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $allowedSorts = ['name', 'code', 'status', 'created_at', 'updated_at'];
        $sortByCandidate = $filters['sort_by'] ?? 'name';
        $sortBy = in_array($sortByCandidate, $allowedSorts, true) ? $sortByCandidate : 'name';
        $sortDir = ($filters['sort_dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($sortBy, $sortDir)->paginate($perPage);
    }

    /**
     * Create a new priority type.
     */
    public function create(array $data): PriorityType
    {
        return PriorityType::create($data);
    }

    /**
     * Find a priority type by ID.
     */
    public function find(int|string $id): ?PriorityType
    {
        return PriorityType::find($id);
    }

    /**
     * Update a priority type.
     */
    public function update(PriorityType $priorityType, array $data): PriorityType
    {
        $priorityType->update($data);

        return $priorityType->refresh();
    }

    /**
     * Delete a priority type.
     */
    public function delete(PriorityType $priorityType): void
    {
        $priorityType->delete();
    }
}
