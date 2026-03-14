<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *   schema="ProductCategoryResource",
 *   type="object",
 *
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="establishment_type_id", type="integer", nullable=true),
 *   @OA\Property(property="establishment_type", ref="#/components/schemas/EstablishmentTypeResource"),
 *   @OA\Property(property="name", type="string"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="status", type="string"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class ProductCategoryResource extends JsonResource
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
            'establishment_type_id' => $this->establishment_type_id,
            'establishment_type' => $this->whenLoaded('establishmentType', function () {
                return new EstablishmentTypeResource($this->establishmentType);
            }),
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
