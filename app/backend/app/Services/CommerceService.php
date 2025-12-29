<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Commerce;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Throwable;

class CommerceService
{
    /**
     * Get paginated commerces
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Commerce::query()->paginate($perPage);
    }

    /**
     * Store a new commerce
     *
     * @throws Throwable
     */
    public function store(array $data): Commerce
    {
        return DB::transaction(function () use ($data) {
            return Commerce::create($data);
        });
    }

    /**
     * Update a commerce
     *
     * @throws Throwable
     */
    public function update(int $commerce_id, array $data): Commerce
    {
        return DB::transaction(function () use ($commerce_id, $data) {
            $commerce = Commerce::findOrFail($commerce_id);
            $commerce->update($data);

            return $commerce->refresh();
        });
    }

    /**
     * Delete a commerce
     *
     * @throws Throwable
     */
    public function delete(int $commerce_id): void
    {
        DB::transaction(function () use ($commerce_id) {
            $commerce = Commerce::findOrFail($commerce_id);
            $commerce->delete();
        });
    }

    /**
     * Show a commerce
     */
    public function show(int $commerce_id): Commerce
    {
        return Commerce::findOrFail($commerce_id);
    }
}
