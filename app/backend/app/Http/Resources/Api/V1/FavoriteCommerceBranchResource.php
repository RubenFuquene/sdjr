<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="FavoriteCommerceBranchResource",
 *     type="object",
 *     @OA\Property(property="orders_count", type="integer", example=12),
 *     @OA\Property(property="commerce_branch", ref="#/components/schemas/CommerceBranchResource")
 * )
 */
class FavoriteCommerceBranchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'orders_count' => (int) ($this['orders_count'] ?? 0),
            'commerce_branch' => new CommerceBranchResource($this['commerce_branch']),
        ];
    }
}