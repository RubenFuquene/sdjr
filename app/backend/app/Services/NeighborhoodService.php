<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Neighborhood;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Throwable;

class NeighborhoodService
{
    /**
     * Get paginated list of neighborhoods.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Neighborhood::with('city')->paginate($perPage);
    }

    /**
     * Store a new neighborhood.
     *
     * @param array $data
     * @return Neighborhood
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
     *
     * @param int $id
     * @return Neighborhood|null
     */
    public function show(int $id): ?Neighborhood
    {
        return Neighborhood::findOrFail($id);
    }

    /**
     * Update a neighborhood.
     *
     * @param int $neighborhood_id
     * @param array $data
     * @return Neighborhood
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
     * @param int $neighborhood_id
     * @return void
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
