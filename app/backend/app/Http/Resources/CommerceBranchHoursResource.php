<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="CommerceBranchHoursResource",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="commerce_branch_id", type="integer", example=10),
 *     @OA\Property(property="day_of_week", type="integer", example=1),
 *     @OA\Property(property="open_time", type="string", example="08:00"),
 *     @OA\Property(property="close_time", type="string", example="18:00"),
 *     @OA\Property(property="note", type="string", example="Horario especial festivos")
 * )
 */
class CommerceBranchHoursResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'commerce_branch_id' => $this->commerce_branch_id,
            'day_of_week' => $this->day_of_week,
            'open_time' => $this->open_time,
            'close_time' => $this->close_time,
            'note' => $this->note,
        ];
    }
}
