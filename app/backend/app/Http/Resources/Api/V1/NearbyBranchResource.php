<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *   schema="NearbyBranchResource",
 *   type="object",
 *   title="Nearby Branch",
 *   description="Sucursal cercana con distancia en kilómetros",
 *
 *   @OA\Property(property="id", type="integer", example=123, description="ID de la sucursal"),
 *   @OA\Property(property="name", type="string", example="Sucursal Centro", description="Nombre de la sucursal"),
 *   @OA\Property(property="address", type="string", example="Av. Reforma 123, CDMX", description="Dirección de la sucursal"),
 *   @OA\Property(property="latitude", type="number", format="float", example=19.4326, description="Latitud de la sucursal"),
 *   @OA\Property(property="longitude", type="number", format="float", example=-99.1332, description="Longitud de la sucursal"),
 *   @OA\Property(property="distance_km", type="number", format="float", example=2.35, description="Distancia en kilómetros desde la ubicación consultada"),
 * )
 */
class NearbyBranchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return array_merge(parent::toArray($request), [
            'distance_km' => round($this->distance_km ?? 0, 2),
        ]);
    }
}
