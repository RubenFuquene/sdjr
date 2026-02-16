<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\CommerceBranchPhoto;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Service for handling CommerceBranch logic
 */
class CommerceBranchService
{
    /**
     * @var DocumentUploadService Document upload service instance.
     */
    protected $documentUploadService;

    /**
     * Constructor
     */
    private CommerceBranchHoursService $commerceBranchHoursService;

    private CommerceBranchPhotoService $commerceBranchPhotoService;

    public function __construct(
        CommerceBranchHoursService $commerceBranchHoursService,
        CommerceBranchPhotoService $commerceBranchPhotoService
    ) {
        $this->commerceBranchHoursService = $commerceBranchHoursService;
        $this->commerceBranchPhotoService = $commerceBranchPhotoService;

        $this->documentUploadService = new DocumentUploadService;
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
                $this->storeCommerceBranchPhotos(
                    $commerceBranch->id,
                    $data['commerce_branch_photos']
                );
            }

            return $commerceBranch->load(['commerce', 'commerceBranchPhotos', 'commerceBranchHours']);
        });
    }

    /**
     * Store commerce branch photos.
     */
    protected function storeCommerceBranchPhotos(int $commerceBranchId, array $photos): void
    {
        $commerce_branch_photos = [];
        foreach ($photos as $photo) {

            $presignedUrlData = $this->documentUploadService->generatePresignedUrl(
                $photo['file_name'],
                $photo['mime_type'],
                $commerceBranchId,
                'commerce_branch_photos'
            );

            $commerce_branch_photos[] = [
                'commerce_branch_id' => $commerceBranchId,
                'file_path' => $presignedUrlData['path'],
                'upload_token' => $presignedUrlData['upload_token'],
                'presigned_url' => $presignedUrlData['presigned_url'],
                'mime_type' => $photo['mime_type'],
                'uploaded_at' => now(),
                'expires_at' => now()->addHour(),
                'uploaded_by_id' => auth()->id(),
                'failed_attempts' => 0,
            ];

        }

        CommerceBranchPhoto::insert($commerce_branch_photos);
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
