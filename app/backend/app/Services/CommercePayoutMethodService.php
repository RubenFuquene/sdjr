<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CommercePayoutMethod;
use Throwable;

/**
 * Service for managing CommercePayoutMethod entities.
 */
class CommercePayoutMethodService
{
    /**
     * Get paginated payout methods with filters.
     *
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function getPaginated(array $filters, int $perPage)
    {
        $query = CommercePayoutMethod::query();

        if (isset($filters['commerce_id'])) {
            $query->where('commerce_id', $filters['commerce_id']);
        }
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (isset($filters['owner_id'])) {
            $query->where('owner_id', $filters['owner_id']);
        }
        if (isset($filters['account_number'])) {
            $query->where('account_number', 'like', "%{$filters['account_number']}%");
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Store a new payout method.
     *
     * @throws Throwable
     */
    public function store(array $data): CommercePayoutMethod
    {
        return CommercePayoutMethod::create($data);
    }

    /**
     * Find a payout method by ID.
     */
    public function find(int $id): ?CommercePayoutMethod
    {
        return CommercePayoutMethod::find($id);
    }

    /**
     * Update a payout method.
     */
    public function update(CommercePayoutMethod $method, array $data): CommercePayoutMethod
    {
        $method->update($data);

        return $method;
    }

    /**
     * Delete a payout method.
     *
     * @throws Throwable
     */
    public function delete(CommercePayoutMethod $method): ?bool
    {
        return $method->delete();
    }
}
