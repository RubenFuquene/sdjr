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
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return EstablishmentType::query()->paginate($perPage);
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
