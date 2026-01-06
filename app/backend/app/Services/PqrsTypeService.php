<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\PqrsType;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for PqrsType business logic
 */
class PqrsTypeService
{
    /**
     * List paginated PqrsTypes with filters
     */
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = PqrsType::query();
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (! empty($filters['name'])) {
            $query->where('name', 'like', '%'.$filters['name'].'%');
        }
        if (! empty($filters['code'])) {
            $query->where('code', 'like', '%'.$filters['code'].'%');
        }

        return $query->paginate($perPage);
    }

    /**
     * Store a new PqrsType
     *
     * @throws Exception
     */
    public function store(array $data): PqrsType
    {
        try {
            return DB::transaction(function () use ($data) {
                return PqrsType::create($data);
            });
        } catch (Exception $e) {
            Log::error('Error creating PqrsType', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Show a PqrsType by id
     *
     * @throws ModelNotFoundException
     */
    public function show(int $id): PqrsType
    {
        try {
            return PqrsType::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Log::warning('PqrsType not found', ['id' => $id]);
            throw $e;
        }
    }

    /**
     * Update a PqrsType
     *
     * @throws Exception
     */
    public function update(int $id, array $data): PqrsType
    {
        try {
            $pqrsType = PqrsType::findOrFail($id);
            $pqrsType->update($data);

            return $pqrsType;
        } catch (Exception $e) {
            Log::error('Error updating PqrsType', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Delete a PqrsType
     *
     * @throws Exception
     */
    public function destroy(int $id): void
    {
        try {
            $pqrsType = PqrsType::findOrFail($id);
            $pqrsType->delete();
        } catch (Exception $e) {
            Log::error('Error deleting PqrsType', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
