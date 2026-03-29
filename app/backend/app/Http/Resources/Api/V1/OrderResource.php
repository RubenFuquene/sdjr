<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="OrderResource",
 *     type="object",
 *     title="Order Resource",
 *     description="Order resource response schema",
 *
 *     @OA\Property(property="id", type="integer", example=1, description="Order ID"),
 *     @OA\Property(property="user_id", type="integer", example=5, description="User ID"),
 *     @OA\Property(property="commerce_branch_id", type="integer", example=2, description="Commerce Branch ID"),
 *     @OA\Property(property="total_price", type="number", format="float", example=199.99, description="Total price of the order"),
 *     @OA\Property(property="status", type="string", example="pending", description="Order status"),
 *     @OA\Property(
 *         property="items",
 *         type="array",
 *
 *         @OA\Items(ref="#/components/schemas/OrderItemResource"),
 *         description="Order items"
 *     ),
 *
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-18T12:34:56Z", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-03-18T12:34:56Z", description="Update timestamp")
 * )
 */
class OrderResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user' => new UserResource($this->user),
            'commerce_branch' => new CommerceBranchResource($this->commerceBranch),
            'total_price' => $this->total_price,
            'status' => $this->status,
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
