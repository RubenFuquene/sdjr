<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\ResourceCollection;

/**
 * @OA\Schema(
 *   schema="ProductResourceCollection",
 *   type="object",
 *
 *   @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/ProductResource")),
 *   @OA\Property(property="meta", type="object",
 *     @OA\Property(property="current_page", type="integer"),
 *     @OA\Property(property="last_page", type="integer"),
 *     @OA\Property(property="per_page", type="integer"),
 *     @OA\Property(property="total", type="integer")
 *   )
 * )
 */
class ProductResourceCollection extends ResourceCollection
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toArray($request): array
    {
        return $this->collection->map(function ($item) {
            return new ProductResource($item);
        })->all();

    }
}
