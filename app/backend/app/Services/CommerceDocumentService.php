<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\CommerceDocument;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Throwable;

/**
 * Service for managing CommerceDocument entities.
 */
class CommerceDocumentService
{
    /**
     * Store a new commerce document.
     *
     * @param array $data
     * @return CommerceDocument
     * @throws Throwable
     */
    public function store(array $data): CommerceDocument
    {
        return CommerceDocument::create($data);
    }

    /**
     * Get all documents for a commerce.
     *
     * @param int $commerceId
     * @return Collection
     */
    public function getByCommerceId(int $commerceId): Collection
    {
        return CommerceDocument::where('commerce_id', $commerceId)->get();
    }

    /**
     * Find a document by ID.
     *
     * @param int $id
     * @return CommerceDocument|null
     */
    public function find(int $id): ?CommerceDocument
    {
        return CommerceDocument::find($id);
    }

    /**
     * Update a commerce document.
     *
     * @param CommerceDocument $document
     * @param array $data
     * @return CommerceDocument
     */
    public function update(CommerceDocument $document, array $data): CommerceDocument
    {
        $document->update($data);
        return $document;
    }

    /**
     * Delete a commerce document.
     *
     * @param CommerceDocument $document
     * @return bool|null
     * @throws Throwable
     */
    public function delete(CommerceDocument $document): ?bool
    {
        return $document->delete();
    }
}
