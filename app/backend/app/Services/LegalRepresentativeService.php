<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\LegalRepresentative;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Service layer for LegalRepresentative business logic.
 */
class LegalRepresentativeService
{
    /**
     * Get paginated list of legal representatives.
     *
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginate(int $perPage = 15): LengthAwarePaginator
    {
        return LegalRepresentative::with('commerce')->paginate($perPage);
    }

    /**
     * Store a new legal representative.
     *
     * @param array $data
     * @return LegalRepresentative
     * @throws Throwable
     */
    public function store(array $data): LegalRepresentative
    {
        return DB::transaction(function () use ($data) {
            return LegalRepresentative::create($data);
        });
    }

    /**
     * Show a legal representative by id.
     *
     * @param int $id
     * @return LegalRepresentative
     * @throws ModelNotFoundException
     */
    public function show(int $id): LegalRepresentative
    {
        return LegalRepresentative::with('commerce')->findOrFail($id);
    }

    /**
     * Update a legal representative.
     *
     * @param int $id
     * @param array $data
     * @return LegalRepresentative
     * @throws Throwable
     */
    public function update(int $id, array $data): LegalRepresentative
    {
        return DB::transaction(function () use ($id, $data) {
            $legalRepresentative = LegalRepresentative::findOrFail($id);
            $legalRepresentative->update($data);
            return $legalRepresentative->fresh(['commerce']);
        });
    }

    /**
     * Delete a legal representative (soft delete).
     *
     * @param int $id
     * @return void
     * @throws Throwable
     */
    public function destroy(int $id): void
    {
        DB::transaction(function () use ($id) {
            $legalRepresentative = LegalRepresentative::findOrFail($id);
            $legalRepresentative->delete();
        });
    }
}
