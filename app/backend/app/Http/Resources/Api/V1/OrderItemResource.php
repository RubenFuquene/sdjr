<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="OrderItemResource",
 *     type="object",
 *     title="Order Item Resource",
 *     description="Order item resource response schema",
 *
 *     @OA\Property(property="id", type="integer", example=1, description="Order item ID"),
 *     @OA\Property(property="order", ref="#/components/schemas/OrderResource", description="Order"),
 *     @OA\Property(property="product", ref="#/components/schemas/ProductResource", description="Product"),
 *     @OA\Property(property="quantity", type="integer", example=2, description="Quantity of the product"),
 *     @OA\Property(property="unit_price", type="number", format="float", example=49.99, description="Unit price of the product"),
 *     @OA\Property(property="subtotal", type="number", format="float", example=99.98, description="Subtotal for this item"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2024-03-18T12:34:56Z", description="Creation timestamp"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2024-03-18T12:34:56Z", description="Update timestamp")
 * )
 */
class OrderItemResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'product_id' => $this->product_id,
            'product' => new ProductResource($this->whenLoaded('product')),
            'quantity' => $this->quantity,
            'unit_price' => $this->unit_price,
            'subtotal' => $this->subtotal,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
