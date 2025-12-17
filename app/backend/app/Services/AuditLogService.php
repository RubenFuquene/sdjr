<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Log;
use Exception;

class AuditLogService
{
    /**
     * Get all audit logs.
     *
     * @return Collection<int, AuditLog>
     */
    public function getAll(): Collection
    {
        try {
            return AuditLog::orderByDesc('created_at')->get();
        } catch (Exception $e) {
            Log::error('Error fetching audit logs', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    /**
     * Get a single audit log by ID.
     *
     * @param int $id
     * @return AuditLog
     * @throws ModelNotFoundException
     */
    public function getById(int $id): AuditLog
    {
        try {
            return AuditLog::findOrFail($id);
        } catch (ModelNotFoundException $e) {
            Log::warning('Audit log not found', ['id' => $id]);
            throw $e;
        } catch (Exception $e) {
            Log::error('Error fetching audit log', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }
}
