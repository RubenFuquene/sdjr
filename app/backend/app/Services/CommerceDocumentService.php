<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CommerceDocument;
use Illuminate\Database\Eloquent\Collection;
use Throwable;

/**
 * Service for managing CommerceDocument entities.
 */
class CommerceDocumentService
{
    /**
     * Store a new commerce document.
     *
     * @throws Throwable
     */
    public function store(array $data): CommerceDocument
    {
        
        return CommerceDocument::create($data);
    }

    /**
     * Get all documents for a commerce.
     */
    public function getByCommerceId(int $commerceId): Collection
    {
        return CommerceDocument::where('commerce_id', $commerceId)->get();
    }

    /**
     * Find a document by ID.
     */
    public function find(int $id): ?CommerceDocument
    {
        return CommerceDocument::find($id);
    }

    /**
     * Update a commerce document.
     */
    public function update(CommerceDocument $document, array $data): CommerceDocument
    {
        $document->update($data);

        return $document;
    }

    /**
     * Delete a commerce document.
     *
     * @throws Throwable
     */
    public function delete(CommerceDocument $document): ?bool
    {
        return $document->delete();
    }
}
