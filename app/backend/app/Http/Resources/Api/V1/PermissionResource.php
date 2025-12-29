<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="PermissionResource",
 *     title="Permission Resource",
 *     description="Permission resource response",
 *     @OA\Property(property="name", type="string", example="users.create"),
 *     @OA\Property(property="description", type="string", example="Permite crear usuarios")
 * )
 */
class PermissionResource extends JsonResource
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
            'name' => $this->name,
            'description' => $this->description,
        ];
    }
}
