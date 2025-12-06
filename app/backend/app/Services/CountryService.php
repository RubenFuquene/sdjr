<?php

namespace App\Services;

use App\Models\Country;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CountryService
{
    /**
     * Get all countries.
     *
     * @return Collection
     */
    public function getAll(): Collection
    {
        return Country::all();
    }

    /**
     * Get paginated countries.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function getPaginated(int $perPage = 15): LengthAwarePaginator
    {
        return Country::paginate($perPage);
    }

    /**
     * Create a new country.
     *
     * @param array $data
     * @return Country
     */
    public function create(array $data): Country
    {
        return Country::create($data);
    }

    /**
     * Find a country by ID.
     *
     * @param string $id
     * @return Country|null
     */
    public function find(string $id): ?Country
    {
        return Country::find($id);
    }

    /**
     * Update a country.
     *
     * @param Country $country
     * @param array $data
     * @return Country
     */
    public function update(Country $country, array $data): Country
    {
        $country->update($data);
        return $country;
    }

    /**
     * Delete a country.
     *
     * @param Country $country
     * @return bool
     */
    public function delete(Country $country): bool
    {
        return $country->delete();
    }
}
