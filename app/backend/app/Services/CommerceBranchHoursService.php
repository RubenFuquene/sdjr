<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CommerceBranchHour;
use Illuminate\Support\Facades\Log;

/**
 * Service for handling CommerceBranchHour logic
 */
class CommerceBranchHoursService
{
    /**
     * Store a new branch hour
     *
     * @param array $data
     * @return CommerceBranchHour
     */
    public function store(array $data): CommerceBranchHour
    {
        try {
            return CommerceBranchHour::create($data);
        } catch (\Throwable $e) {
            Log::error('Error storing branch hour', ['error' => $e->getMessage()]);
            throw $e;
        }
    }
}
