<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Bank;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class BankService
{
    /**
     * Get paginated banks with optional filters.
     */
    public function getPaginated(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = Bank::query();
        if (!empty($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }
        if (!empty($filters['code'])) {
            $query->where('code', 'like', "%{$filters['code']}%");
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        return $query->paginate($perPage);
    }

    /**
     * Store a new bank.
     */
    public function store(array $data): Bank
    {
        return Bank::create($data);
    }

    /**
     * Get a single bank by ID.
     * @throws ModelNotFoundException
     */
    public function find(int $id): Bank
    {
        return Bank::findOrFail($id);
    }

    /**
     * Update a bank.
     * @throws ModelNotFoundException
     */
    public function update(int $id, array $data): Bank
    {
        $bank = $this->find($id);
        $bank->update($data);
        return $bank->refresh();
    }

    /**
     * Delete a bank (soft delete).
     * @throws ModelNotFoundException
     */
    public function delete(int $id): void
    {
        $bank = $this->find($id);
        $bank->delete();
    }
}
