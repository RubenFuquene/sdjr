<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *   schema="NearbyProductResource",
 *   type="object",
 *   title="Nearby Product",
 *   description="Producto disponible en sucursal cercana con distancia a la sucursal más próxima",
 *
 *   @OA\Property(property="id", type="integer", example=456, description="ID del producto"),
 *   @OA\Property(property="name", type="string", example="Coca Cola 600ml", description="Nombre del producto"),
 *   @OA\Property(property="price", type="number", format="float", example=18.50, description="Precio del producto"),
 *   @OA\Property(property="category_id", type="integer", example=5, description="ID de la categoría"),
 *   @OA\Property(property="nearest_branch_distance_km", type="number", format="float", example=1.75, description="Distancia en kilómetros a la sucursal más cercana con stock"),
 *   @OA\Property(property="nearest_branch", ref="#/components/schemas/NearbyBranchResource", description="Sucursal más cercana con stock disponible"),
 * )
 */
class NearbyProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        $nearestBranch = $this->commerceBranches->first();

        return array_merge(parent::toArray($request), [
            'nearest_branch_distance_km' => round($this->nearest_branch_distance_km ?? 0, 2),
            'nearest_branch' => $nearestBranch ? new NearbyBranchResource($nearestBranch) : null,
        ]);
    }
}
