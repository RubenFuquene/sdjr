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
     */
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = LegalRepresentative::with('commerce');

        if (! empty($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $allowedSorts = ['name', 'status', 'created_at', 'updated_at'];
        $sortByCandidate = $filters['sort_by'] ?? 'name';
        $sortBy = in_array($sortByCandidate, $allowedSorts, true) ? $sortByCandidate : 'name';
        $sortDir = ($filters['sort_dir'] ?? 'asc') === 'desc' ? 'desc' : 'asc';

        return $query->orderBy($sortBy, $sortDir)->paginate($perPage);
    }

    /**
     * Store a new legal representative.
     *
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
     * @throws ModelNotFoundException
     */
    public function show(int $id): LegalRepresentative
    {
        return LegalRepresentative::with('commerce')->findOrFail($id);
    }

    /**
     * Update a legal representative.
     *
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
