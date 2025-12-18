<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Commerce;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Throwable;

class CommerceService
{
    /**
     * Get paginated commerces
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return Commerce::with(['ownerUser','legalRepresentatives'])->paginate($perPage);
    }

    /**
     * Store a new commerce
     * @param array $data
     * @return Commerce
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
     * @param int $commerce_id
     * @param array $data
     * @return Commerce
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
     * @param int $commerce_id
     * @return void
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
     * @param int $commerce_id
     * @return Commerce
     */
    public function show(int $commerce_id): Commerce
    {
        return Commerce::with(['ownerUser','legalRepresentatives'])->findOrFail($commerce_id);
    }

    /**
     * Update the active status of a commerce.
     *
     * @param int $commerce_id
     * @param int $is_active
     * @return Commerce
     */
    public function updateStatus(int $commerce_id, int $is_active): Commerce
    {
        $commerce = Commerce::findOrFail($commerce_id);
        $commerce->is_active = $is_active;
        $commerce->save();
        return $commerce;
    }
}
