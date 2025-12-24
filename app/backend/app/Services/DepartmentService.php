<?php

namespace App\Services;

use App\Models\Department;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class DepartmentService
{
    /**
     * Get all departments.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Department::all();
    }

    /**
     * Get paginated departments.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage = 15, string $status = 'all'): LengthAwarePaginator
    {
        $query = Department::with('country');
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        return $query->paginate($perPage);
    }

    /**
     * Create a new department.
     *
     * @param array $data
     * @return Department
     */
    public function create(array $data): Department
    {
        return Department::create($data);
    }

    /**
     * Find a department by ID.
     *
     * @param string $id
     * @return Department|null
     */
    public function find(string $id): ?Department
    {
        return Department::with('country')->find($id);
    }

    /**
     * Update a department.
     *
     * @param Department $department
     * @param array $data
     * @return Department
     */
    public function update(Department $department, array $data): Department
    {
        $department->update($data);
        return $department;
    }

    /**
     * Delete a department.
     *
     * @param Department $department
     * @return bool
     */
    public function delete(Department $department): bool
    {
        return $department->delete();
    }
}
