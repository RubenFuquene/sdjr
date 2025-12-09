<?php

namespace App\Services;

use App\Models\City;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CityService
{
    /**
     * Get all cities.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return City::all();
    }

    /**
     * Get paginated cities.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return City::with('department')->paginate($perPage);
    }

    /**
     * Create a new city.
     *
     * @param array $data
     * @return City
     */
    public function create(array $data): City
    {
        return City::create($data);
    }

    /**
     * Find a city by ID.
     *
     * @param string $id
     * @return City|null
     */
    public function find(string $id): ?City
    {
        return City::with('department')->find($id);
    }

    /**
     * Update a city.
     *
     * @param City $city
     * @param array $data
     * @return City
     */
    public function update(City $city, array $data): City
    {
        $city->update($data);
        return $city;
    }

    /**
     * Delete a city.
     *
     * @param City $city
     * @return bool
     */
    public function delete(City $city): bool
    {
        return $city->delete();
    }
}
