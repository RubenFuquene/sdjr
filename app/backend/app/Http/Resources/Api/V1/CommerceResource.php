<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="CommerceResource",
 *     title="Commerce Resource",
 *     description="Commerce resource representation",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="owner_user_id", type="integer", example=1),
 *     @OA\Property(property="department_id", type="integer", example=1),
 *     @OA\Property(property="city_id", type="integer", example=1),
 *     @OA\Property(property="neighborhood_id", type="integer", example=1),
 *     @OA\Property(property="legal_representatives", type="array", @OA\Items(ref="#/components/schemas/LegalRepresentativeResource")),
 *     @OA\Property(property="name", type="string", example="Comercial S.A.S"),
 *     @OA\Property(property="description", type="string", example="Comercio de tecnología"),
 *     @OA\Property(property="tax_id", type="string", example="900123456"),
 *     @OA\Property(property="tax_id_type", type="string", example="NIT"),
 *     @OA\Property(property="address", type="string", example="Calle 123 #45-67"),
 *     @OA\Property(property="phone", type="string", example="3001234567"),
 *     @OA\Property(property="email", type="string", example="info@comercial.com"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="is_verified", type="boolean", example=false),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-15T12:34:56Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-15T12:34:56Z")
 * )
 */
class CommerceResource extends JsonResource
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
            'owner_user' => new UserResource($this->ownerUser),
            'department' => new DepartmentResource($this->department),
            'city' => new CityResource($this->city),
            'neighborhood' => new NeighborhoodResource($this->neighborhood),
            'legal_representatives' => new LegalRepresentativeResourceCollection($this->legalRepresentativesActive),
            'name' => $this->name,
            'description' => $this->description,
            'tax_id' => $this->tax_id,
            'tax_id_type' => $this->tax_id_type,
            'address' => $this->address,
            'phone' => $this->phone,
            'email' => $this->email,
            'is_active' => $this->is_active,
            'is_verified' => $this->is_verified,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
