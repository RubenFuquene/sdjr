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
     *      example="9d21b3a0-5e1a-4b3a-9b3a-1b3a05e1a4b3"
     * )
     *
     * @OA\Property(
     *      property="department_id",
     *      title="department_id",
     *      description="Department ID",
     *      example="9d21b3a0-5e1a-4b3a-9b3a-1b3a05e1a4b3"
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
            'name' => $this->name,
            'status' => $this->status,
            'department' => new DepartmentResource($this->whenLoaded('department')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
