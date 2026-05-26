<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="CommerceBranchUserResource",
 *     title="Commerce Branch User Resource",
 *     description="Branch leader user resource with assigned branches information",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Juan"),
 *     @OA\Property(property="last_name", type="string", example="Pérez"),
 *     @OA\Property(property="email", type="string", format="email", example="juan.perez@example.com"),
 *     @OA\Property(property="phone", type="string", example="3001234567"),
 *     @OA\Property(property="roles", type="array", @OA\Items(type="string"), example={"branch_leader"}),
 *     @OA\Property(property="status", type="string", example="A"),
 *     @OA\Property(property="assigned_branches", type="array", @OA\Items(ref="#/components/schemas/CommerceBranchResource")),
 *     @OA\Property(property="assignment_date", type="string", format="date-time", example="2023-01-01T12:00:00Z", description="Date when user was assigned to branch"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2023-01-01T12:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2023-01-01T12:00:00Z")
 * )
 */
class CommerceBranchUserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'roles' => $this->roles->pluck('name'),
            'status' => $this->status,
            'assigned_branches' => CommerceBranchResource::collection($this->whenLoaded('assignedBranches')),
            'assignment_date' => $this->whenPivotLoaded('commerce_branch_users', function () {
                return $this->pivot->created_at;
            }),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
