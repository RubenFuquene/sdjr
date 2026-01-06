<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DepartmentService
{
    /**
     * Get all departments.
     */
    public function getAll(): Collection
    {
        return Department::all();
    }

    /**
     * Get paginated departments.
     */
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Department::with('country');
        if (! empty($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }
        if (! empty($filters['code'])) {
            $query->where('code', 'like', "%{$filters['code']}%");
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new department.
     */
    public function create(array $data): Department
    {
        return Department::create($data);
    }

    /**
     * Find a department by ID.
     */
    public function find(string $id): ?Department
    {
        return Department::with('country')->findOrFail($id);
    }

    /**
     * Update a department.
     */
    public function update(Department $department, array $data): Department
    {
        $department->update($data);

        return $department;
    }

    /**
     * Delete a department.
     */
    public function delete(Department $department): bool
    {
        return $department->delete();
    }
}
