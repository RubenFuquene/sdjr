<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *      title="Country Resource",
 *      description="Country resource representation",
 *
 *      @OA\Xml(
 *          name="CountryResource"
 *      )
 * )
 */
class CountryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @OA\Property(
     *      property="id",
     *      title="id",
     *      description="Country ID",
     *      type="integer",
     *      example=1
     * )
     * @OA\Property(
     *      property="name",
     *      title="name",
     *      description="Country name",
     *      example="Colombia"
     * )
     * @OA\Property(
     *      property="status",
     *      title="status",
     *      description="Country status",
     *      example="A"
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
     * @return array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $this->name,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
