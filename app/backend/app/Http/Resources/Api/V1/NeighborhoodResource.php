<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *      title="Neighborhood Resource",
 *      description="Neighborhood resource representation",
 *
 *      @OA\Xml(
 *          name="NeighborhoodResource"
 *      )
 * )
 */
class NeighborhoodResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @OA\Property(
     *      property="id",
     *      title="id",
     *      description="Neighborhood ID",
     *      type="integer",
     *      example=1
     * )
     * @OA\Property(
     *      property="city_id",
     *      title="city_id",
     *      description="City ID",
     *      type="integer",
     *      example=1
     * )
     * @OA\Property(
     *      property="name",
     *      title="name",
     *      description="Neighborhood name",
     *      example="Chapinero"
     * )
     * @OA\Property(
     *      property="status",
     *      title="status",
     *      description="Neighborhood status",
     *      example="A"
     * )
     * @OA\Property(
     *      property="city",
     *      ref="#/components/schemas/CityResource"
     * )
     * @OA\Property(
     *      property="created_at",
     *      title="created_at",
     *      description="Created at timestamp",
     *      example="2023-01-01T00:00:00.000000Z"
     * )
     * @OA\Property(
     *      property="updated_at",
     *      title="updated_at",
     *      description="Updated at timestamp",
     *      example="2023-01-01T00:00:00.000000Z"
     * )
     *
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'city_id' => $this->city_id,
            'name' => $this->name,
            'code' => $this->code,
            'city' => new CityResource($this->whenLoaded('city')),
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
