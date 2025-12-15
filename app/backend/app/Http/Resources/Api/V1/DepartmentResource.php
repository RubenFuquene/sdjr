<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *      title="Department Resource",
 *      description="Department resource representation",
 *      @OA\Xml(
 *          name="DepartmentResource"
 *      )
 * )
 */
class DepartmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
    * @OA\Property(
    *      property="id",
    *      title="id",
    *      description="Department ID",
    *      type="integer",
    *      example=1
    * )
     *
    * @OA\Property(
    *      property="country_id",
    *      title="country_id",
    *      description="Country ID",
    *      type="integer",
    *      example=1
    * )
     *
     * @OA\Property(
     *      property="name",
     *      title="name",
     *      description="Department name",
     *      example="Cundinamarca"
     * )
     *
     * @OA\Property(
     *      property="status",
     *      title="status",
     *      description="Department status",
     *      example="A"
     * )
     *
     * @OA\Property(
     *      property="country",
     *      ref="#/components/schemas/CountryResource"
     * )
     *
     * @OA\Property(
     *      property="created_at",
     *      title="created_at",
     *      description="Created at timestamp",
     *      example="2023-01-01T00:00:00.000000Z"
     * )
     *
     * @OA\Property(
     *      property="updated_at",
     *      title="updated_at",
     *      description="Updated at timestamp",
     *      example="2023-01-01T00:00:00.000000Z"
     * )
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'country_id' => $this->country_id,
            'code' => $this->code,
            'name' => $this->name,
            'status' => $this->status,
            'country' => new CountryResource($this->whenLoaded('country')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
