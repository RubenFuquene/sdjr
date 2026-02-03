<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *   schema="ProductResource",
 *   type="object",
 *
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="commerce_id", type="integer"),
 *   @OA\Property(property="product_category_id", type="integer"),
 *   @OA\Property(property="title", type="string"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="product_type", type="string", enum={"single","package"}),
 *   @OA\Property(property="original_price", type="number", format="float"),
 *   @OA\Property(property="discounted_price", type="number", format="float", nullable=true),
 *   @OA\Property(property="quantity_total", type="integer"),
 *   @OA\Property(property="quantity_available", type="integer"),
 *   @OA\Property(property="expires_at", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="status", type="string"),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class ProductResource extends JsonResource
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
            'id' => $this->id,
            'commerce_id' => $this->commerce_id,
            'product_category_id' => $this->product_category_id,
            'title' => $this->title,
            'description' => $this->description,
            'product_type' => $this->product_type,
            'original_price' => $this->original_price,
            'discounted_price' => $this->discounted_price,
            'quantity_total' => $this->quantity_total,
            'quantity_available' => $this->quantity_available,
            'expires_at' => $this->expires_at,
            'photos' => $this->whenLoaded('photos', function () {
                return $this->photos->map(function ($photo) {
                    return new DocumentUploadResource($photo, ['product_id' => $this->id]);
                });
            }),
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

        ];
    }
}
