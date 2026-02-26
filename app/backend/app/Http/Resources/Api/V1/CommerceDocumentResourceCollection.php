<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @OA\Schema(
 *     schema="CommerceDocumentResourceCollection",
 *     title="Commerce Document Resource Collection",
 *     description="Collection of commerce documents",
 *
 *     @OA\Property(
 *         property="data",
 *         type="array",
 *
 *         @OA\Items(ref="#/components/schemas/CommerceDocumentResource")
 *     )
 * )
 */
class CommerceDocumentResourceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toArray($request): array
    {
        return $this->collection->map(function ($item) {
            return new CommerceDocumentResource($item);
        })->all();

    }
}
