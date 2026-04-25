<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\EstablishmentType;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Throwable;

class EstablishmentTypeService
{
    /**
     * Get paginated establishment types
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = EstablishmentType::query();

        if (! empty($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $allowedSorts = ['name', 'status', 'created_at', 'updated_at'];
        $sortByCandidate = $filters['sort_by'] ?? 'name';
        $sortBy = in_array($sortByCandidate, $allowedSorts, true) ? $sortByCandidate : 'name';
        $sortDir = ($filters['sort_dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($sortBy, $sortDir)->paginate($perPage);
    }

    /**
     * Store a new establishment type
     *
     * @throws Throwable
     */
    public function store(array $data): EstablishmentType
    {
        return DB::transaction(function () use ($data) {
            return EstablishmentType::create($data);
        });
    }

    /**
     * Update an establishment type
     *
     * @throws Throwable
     */
    public function update(int $establishmentType_id, array $data): EstablishmentType
    {
        return DB::transaction(function () use ($establishmentType_id, $data) {
            $establishmentType = EstablishmentType::findOrFail($establishmentType_id);
            $establishmentType->update($data);

            return $establishmentType->refresh();
        });
    }

    /**
     * Delete an establishment type
     *
     * @throws Throwable
     */
    public function delete(int $establishmentType_id): void
    {
        DB::transaction(function () use ($establishmentType_id) {
            $establishmentType = EstablishmentType::findOrFail($establishmentType_id);
            $establishmentType->delete();
        });
    }

    /**
     * Show an establishment type
     */
    public function show(int $establishmentType_id): EstablishmentType
    {
        return EstablishmentType::findOrFail($establishmentType_id);
    }
}
