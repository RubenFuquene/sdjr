<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="CommerceBranchResource",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="commerce_id", type="integer", example=5),
 *     @OA\Property(property="name", type="string", example="Sucursal Norte"),
 *     @OA\Property(property="address", type="string", example="Calle 123 #45-67"),
 *     @OA\Property(property="department", type="string", example="Cundinamarca"),
 *     @OA\Property(property="city", type="string", example="Bogotá"),
 *     @OA\Property(property="neighborhood", type="string", example="Chapinero"),
 *     @OA\Property(property="latitude", type="number", format="float", example=4.6097),
 *     @OA\Property(property="longitude", type="number", format="float", example=-74.0817),
 *     @OA\Property(property="phone", type="string", example="3001234567"),
 *     @OA\Property(property="email", type="string", example="norte@comercio.com"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class CommerceBranchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'commerce_id' => $this->commerce_id,
            'photos' => CommerceBranchPhotoResource::collection($this->whenLoaded('commerceBranchPhotos')),
            'hours' => CommerceBranchHoursResource::collection($this->whenLoaded('commerceBranchHours')),
            'name' => $this->name,
            'address' => $this->address,
            'department' => $this->department?->name,
            'city' => $this->city?->name,
            'neighborhood' => $this->neighborhood?->name,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'phone' => $this->phone,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
