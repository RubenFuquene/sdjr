<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Commerce;
use App\Models\CommerceBranch;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for handling CommerceBranch logic
 */
class CommerceBranchService
{
    private CommerceBranchHoursService $commerceBranchHoursService;

    private CommerceBranchPhotoService $commerceBranchPhotoService;

    public function __construct(
        CommerceBranchHoursService $commerceBranchHoursService,
        CommerceBranchPhotoService $commerceBranchPhotoService
    ) {
        $this->commerceBranchHoursService = $commerceBranchHoursService;
        $this->commerceBranchPhotoService = $commerceBranchPhotoService;
    }

    /**
     * Get paginated branches for a commerce
     *
     * @throws ModelNotFoundException
     */
    public function getBranchesByCommerceId(int $commerceId, int $perPage = 15): LengthAwarePaginator
    {
        try {
            if (isset($commerceId)) {
                if (! Commerce::where('id', $commerceId)->exists()) {
                    throw new ModelNotFoundException('Commerce not found');
                }
            }

            return CommerceBranch::where('commerce_id', $commerceId)
                ->with(['department', 'city', 'neighborhood', 'commerceBranchHours', 'commerceBranchPhotos'])
                ->paginate($perPage);

        } catch (ModelNotFoundException $e) {
            Log::error('Commerce not found for branches', ['commerce_id' => $commerceId]);
            throw $e;
        }
    }

    /**
     * Paginate all branches
     */
    public function getPaginated(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = CommerceBranch::with(['department', 'city', 'neighborhood', 'commerceBranchHours', 'commerceBranchPhotos']);

        if (! empty($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }
        if (! empty($filters['address'])) {
            $query->where('address', 'like', "%{$filters['address']}%");
        }
        if (! empty($filters['longitude'])) {
            $query->where('longitude', $filters['longitude']);
        }
        if (! empty($filters['latitude'])) {
            $query->where('latitude', $filters['latitude']);
        }
        if (! empty($filters['phone'])) {
            $query->where('phone', 'like', "%{$filters['phone']}%");
        }
        if (! empty($filters['email'])) {
            $query->where('email', 'like', "%{$filters['email']}%");
        }
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->paginate($perPage);
    }

    /**
     * Store a new branch
     */
    public function store(array $data): CommerceBranch
    {
        return DB::transaction(function () use ($data) {
            $commerceBranchData = $data['commerce_branch'];
            $commerceBranch = CommerceBranch::create($commerceBranchData);

            if (! empty($data['commerce_branch_hours'])) {
                $commerceBranchHoursData = $data['commerce_branch_hours'];
                $commerceBranchHoursData['commerce_branch_id'] = $commerceBranch->id;
                $this->commerceBranchHoursService->store($commerceBranchHoursData);
            }

            if (! empty($data['commerce_branch_photos'])) {
                $commerceBranchPhotosData = $data['commerce_branch_photos'];
                $commerceBranchPhotosData['commerce_branch_id'] = $commerceBranch->id;
                $this->commerceBranchPhotoService->store($commerceBranchPhotosData);
            }

            return $commerceBranch->load(['commerce', 'commerceBranchPhotos', 'commerceBranchHours']);
        });
    }

    /**
     * Show a branch by id
     */
    public function show(int $id): CommerceBranch
    {
        return CommerceBranch::with(['department', 'city', 'neighborhood'])->findOrFail($id);
    }

    /**
     * Update a branch
     */
    public function update(int $id, array $data): CommerceBranch
    {
        $branch = CommerceBranch::findOrFail($id);
        $branch->update($data);

        return $branch->fresh(['department', 'city', 'neighborhood']);
    }

    /**
     * Delete a branch (soft delete)
     */
    public function destroy(int $id): void
    {
        $branch = CommerceBranch::findOrFail($id);
        $branch->delete();
    }
}
