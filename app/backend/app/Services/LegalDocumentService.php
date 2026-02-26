<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\Constant;
use App\Models\LegalDocument;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class LegalDocumentService
{
    /**
     * Get paginated legal documents with filters.
     */
    public function getPaginated(array $filters, int $perPage): LengthAwarePaginator
    {
        $query = LegalDocument::query();
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderByDesc('effective_date')->paginate($perPage);
    }

    /**
     * Get the latest active legal document by type.
     *
     * @param  string  $type  The type of legal document to retrieve (e.g., LEGAL_DOCUMENT_TYPE_TERMS)
     * @return LegalDocument The latest active legal document
     *
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException If no active legal document is found
     */
    public function getLatestByType(string $type): LegalDocument
    {
        return LegalDocument::where('type', $type)
            ->where('status', Constant::LEGAL_DOCUMENT_STATUS_ACTIVE)
            ->orderByDesc('effective_date')
            ->firstOrFail();
    }

    /**
     * Store a new legal document.
     *
     * @param array $data
     * @return LegalDocument
     * @throws \Throwable
     */
    public function store(array $data): LegalDocument
    {
        try {
            return LegalDocument::create($data);
        } catch (\Throwable $e) {
            throw $e;
        }
    }
}
