<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="LegalRepresentativeResource",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="commerce_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Juan"),
 *     @OA\Property(property="last_name", type="string", example="Pérez"),
 *     @OA\Property(property="document", type="string", example="1234567890"),
 *     @OA\Property(property="document_type", type="string", example="CC"),
 *     @OA\Property(property="email", type="string", example="juan.perez@example.com"),
 *     @OA\Property(property="phone", type="string", example="3001234567"),
 *     @OA\Property(property="is_primary", type="boolean", example=true),
 *     @OA\Property(property="status", type="string", example="1"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", nullable=true),
 *     @OA\Property(property="commerce", ref="#/components/schemas/CommerceResource")
 * )
 */
class LegalRepresentativeResource extends JsonResource
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
            'commerce_id' => $this->commerce_id,
            'name' => $this->name,
            'last_name' => $this->last_name,
            'document' => $this->document,
            'document_type' => $this->document_type,
            'email' => $this->email,
            'phone' => $this->phone,
            'is_primary' => $this->is_primary,
            'status' => $this->status,
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
            'deleted_at' => $this->deleted_at?->toISOString(),
            'commerce' => new CommerceResource($this->whenLoaded('commerce')),
        ];
    }
}
