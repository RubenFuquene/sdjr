<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Neighborhood;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Throwable;

class NeighborhoodService
{
    /**
     * Get paginated list of neighborhoods.
     */
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = Neighborhood::with('city');
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
     * Store a new neighborhood.
     *
     * @throws Throwable
     */
    public function store(array $data): Neighborhood
    {
        return DB::transaction(function () use ($data) {
            return Neighborhood::create($data);
        });
    }

    /**
     * Show a specific neighborhood.
     */
    public function show(int $id): ?Neighborhood
    {
        return Neighborhood::findOrFail($id);
    }

    /**
     * Update a neighborhood.
     *
     * @throws Throwable
     */
    public function update(int $neighborhood_id, array $data): Neighborhood
    {
        return DB::transaction(function () use ($neighborhood_id, $data) {
            $neighborhood = Neighborhood::findOrFail($neighborhood_id);
            $neighborhood->update($data);

            return $neighborhood;
        });
    }

    /**
     * Delete a neighborhood.
     *
     * @throws Throwable
     */
    public function destroy(int $neighborhood_id): void
    {
        DB::transaction(function () use ($neighborhood_id) {
            $neighborhood = Neighborhood::findOrFail($neighborhood_id);
            $neighborhood->delete();
        });
    }
}
