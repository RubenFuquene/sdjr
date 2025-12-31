<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\SupportStatus;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class SupportStatusService
{
    /**
     * Get paginated support statuses with optional filters.
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = SupportStatus::query();
        if (!empty($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }
        if (!empty($filters['code'])) {
            $query->where('code', 'like', "%{$filters['code']}%");
        }
        if (!empty($filters['color'])) {
            $query->where('color', 'like', "%{$filters['color']}%");
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        return $query->paginate($perPage);
    }

    /**
     * Store a new support status.
     */
    public function store(array $data): SupportStatus
    {
        return SupportStatus::create($data);
    }

    /**
     * Get a single support status by ID.
     * @throws ModelNotFoundException
     */
    public function find(int $id): SupportStatus
    {
        return SupportStatus::findOrFail($id);
    }

    /**
     * Update a support status.
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): SupportStatus
    {
        $status = $this->find($id);
        $status->update($data);
        return $status->refresh();
    }

    /**
     * Delete a support status (soft delete).
     * @throws ModelNotFoundException
     */
    public function delete(int $id): void
    {
        $status = $this->find($id);
        $status->delete();
    }
}
