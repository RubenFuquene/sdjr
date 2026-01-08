<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CommercePayoutMethod;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Throwable;

/**
 * Service for managing CommercePayoutMethod entities.
 */
class CommercePayoutMethodService
{
    /**
     * Get paginated payout methods with filters.
     *
     * @param array $filters
     * @param int $perPage
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
     * @param array $data
     * @return CommercePayoutMethod
     * @throws Throwable
     */
    public function store(array $data): CommercePayoutMethod
    {
        return CommercePayoutMethod::create($data);
    }

    /**
     * Find a payout method by ID.
     *
     * @param int $id
     * @return CommercePayoutMethod|null
     */
    public function find(int $id): ?CommercePayoutMethod
    {
        return CommercePayoutMethod::find($id);
    }

    /**
     * Update a payout method.
     *
     * @param CommercePayoutMethod $method
     * @param array $data
     * @return CommercePayoutMethod
     */
    public function update(CommercePayoutMethod $method, array $data): CommercePayoutMethod
    {
        $method->update($data);
        return $method;
    }

    /**
     * Delete a payout method.
     *
     * @param CommercePayoutMethod $method
     * @return bool|null
     * @throws Throwable
     */
    public function delete(CommercePayoutMethod $method): ?bool
    {
        return $method->delete();
    }
}
