<?php

namespace App\Services;

use App\Models\Country;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CountryService
{
    /**
     * Get all countries.
     */
    public function getAll(): Collection
    {
        return Country::all();
    }

    /**
     * Get paginated countries with optional status filter.
     */
    public function getPaginated(int $perPage = 15, string $status = 'all'): LengthAwarePaginator
    {
        $query = Country::query();
        if ($status !== 'all') {
            $query->where('status', $status);
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new country.
     */
    public function create(array $data): Country
    {
        return Country::create($data);
    }

    /**
     * Find a country by ID.
     */
    public function find(string $id): ?Country
    {
        return Country::find($id);
    }

    /**
     * Update a country.
     */
    public function update(Country $country, array $data): Country
    {
        $country->update($data);

        return $country;
    }

    /**
     * Delete a country.
     */
    public function delete(Country $country): bool
    {
        return $country->delete();
    }
}
