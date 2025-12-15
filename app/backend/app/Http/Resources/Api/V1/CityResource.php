<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *      title="City Resource",
 *      description="City resource representation",
 *      @OA\Xml(
 *          name="CityResource"
 *      )
 * )
 */
class CityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
    * @OA\Property(
    *      property="id",
    *      title="id",
    *      description="City ID",
    *      type="integer",
    *      example=1
    * )
     *
    * @OA\Property(
    *      property="department_id",
    *      title="department_id",
    *      description="Department ID",
    *      type="integer",
    *      example=1
    * )
     *
     * @OA\Property(
     *      property="name",
     *      title="name",
     *      description="City name",
     *      example="Bogota"
     * )
     *
     * @OA\Property(
     *      property="status",
     *      title="status",
     *      description="City status",
     *      example="A"
     * )
     *
     * @OA\Property(
     *      property="department",
     *      ref="#/components/schemas/DepartmentResource"
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
            'department_id' => $this->department_id,
            'code' => $this->code,
            'name' => $this->name,
            'status' => $this->status,
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
