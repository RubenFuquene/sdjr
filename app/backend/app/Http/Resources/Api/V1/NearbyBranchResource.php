<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

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
 *   @OA\Property(property="quantity_available", type="integer", nullable=true, description="Stock del producto consultado en ESTA sede (SCRUM-277). Ausente si el recurso se sirve fuera de un contexto de producto (ej. /nearby/branches)."),
 * )
 */
/**
 * Extiende CommerceBranchResource (no JsonResource) para heredar la
 * serialización real de la sucursal (commerce_name, hours, department/city/
 * neighborhood por nombre, photos) en vez de volcar el modelo Eloquent crudo.
 */
class NearbyBranchResource extends CommerceBranchResource
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
            // SCRUM-277 Fase 1: cuando este resource se sirve como "nearest_branch"
            // de un producto (NearbyProductResource), el modelo trae el pivote
            // producto-sede cargado — es el stock real que el cliente debe ver y
            // el que limita cuánto puede comprar. products.quantity_available (a
            // nivel de producto) quedó vestigial para single; leer de ahí mostraría
            // 0 siempre. Ausente (no `?? 0`) cuando no hay contexto de producto,
            // para no fabricar un número donde no aplica.
            'quantity_available' => $this->pivot->quantity_available ?? null,
        ]);
    }
}
