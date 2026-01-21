<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CommerceBranchPhoto;
use Illuminate\Support\Facades\Log;

/**
 * Service for handling CommerceBranchPhoto logic
 */
class CommerceBranchPhotoService
{
    /**
     * Store a new branch photo
     *
     * @param array $data
     * @return CommerceBranchPhoto
     */
    public function store(array $data): CommerceBranchPhoto
    {
        try {
            return CommerceBranchPhoto::create($data);
        } catch (\Throwable $e) {
            Log::error('Error storing branch photo', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
