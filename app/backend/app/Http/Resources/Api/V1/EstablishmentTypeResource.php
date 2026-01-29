<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="EstablishmentTypeResource",
 *     title="EstablishmentType Resource",
 *     description="EstablishmentType resource representation",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Restaurante"),
 *     @OA\Property(property="code", type="string", example="REST"),
 *     @OA\Property(property="status", type="string", example="1"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-15T12:34:56Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-15T12:34:56Z")
 * )
 */
class EstablishmentTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
