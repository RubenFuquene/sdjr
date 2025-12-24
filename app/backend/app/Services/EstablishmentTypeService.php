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
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return EstablishmentType::query()->paginate($perPage);
    }

    /**
     * Store a new establishment type
     * @param array $data
     * @return EstablishmentType
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
     * @param int $establishmentType_id
     * @param array $data
     * @return EstablishmentType
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
     * @param int $establishmentType_id
     * @return void
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
     * @param int $establishmentType_id
     * @return EstablishmentType
     */
    public function show(int $establishmentType_id): EstablishmentType
    {
        return EstablishmentType::findOrFail($establishmentType_id);
    }
}
