<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @OA\Schema(
 *     schema="LegalRepresentativeResourceCollection",
 *     title="Legal Representative Resource Collection",
 *     description="Collection of legal representatives",
 *
 *     @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/LegalRepresentativeResource"))
 * )
 */
class LegalRepresentativeResourceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            $this->collection->map(function ($item) {
                return new LegalRepresentativeResource($item);
            }),
        ];
    }
}
