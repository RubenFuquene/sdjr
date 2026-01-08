<?php

namespace App\Services;

use App\Models\City;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CityService
{
    /**
     * Get all cities.
     */
    public function getAll(): Collection
    {
        return City::all();
    }

    /**
     * Get paginated cities.
     */
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = City::with('department');
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
     * Create a new city.
     */
    public function create(array $data): City
    {
        return City::create($data);
    }

    /**
     * Find a city by ID.
     */
    public function find(string $id): ?City
    {
        return City::with('department')->find($id);
    }

    /**
     * Update a city.
     */
    public function update(City $city, array $data): City
    {
        $city->update($data);

        return $city;
    }

    /**
     * Delete a city.
     */
    public function delete(City $city): bool
    {
        return $city->delete();
    }
}
