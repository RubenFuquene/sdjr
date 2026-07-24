<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Constants\Constant;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *   schema="ProductResource",
 *   type="object",
 *
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="commerce_id", type="integer"),
 *   @OA\Property(property="commerce_name", type="string", nullable=true, description="Nombre del comercio propietario (solo si la relacion commerce esta cargada)"),
 *   @OA\Property(property="product_category_id", type="integer"),
 *   @OA\Property(property="category", type="string", nullable=true, description="Nombre de la categoria (solo si la relacion category esta cargada)"),
 *   @OA\Property(property="title", type="string"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="product_type", type="string", enum={"single","package"}),
 *   @OA\Property(property="original_price", type="number", format="float"),
 *   @OA\Property(property="discounted_price", type="number", format="float", nullable=true),
 *   @OA\Property(property="quantity_total", type="integer"),
 *   @OA\Property(property="quantity_available", type="integer"),
 *   @OA\Property(property="available_for_packaging", type="integer", nullable=true, description="Stock still available to be committed to packages (only present for product_type=single)"),
 *   @OA\Property(property="expires_at", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="photos", type="array", @OA\Items(ref="#/components/schemas/DocumentUploadResource")),
 *   @OA\Property(
 *     property="commerce_branches",
 *     type="array",
 *     description="Sucursales asignadas al producto (solo si la relacion commerceBranches esta cargada)",
 *
 *     @OA\Items(
 *       type="object",
 *
 *       @OA\Property(property="id", type="integer"),
 *       @OA\Property(property="name", type="string")
 *     )
 *   ),
 *   @OA\Property(
 *     property="package_items",
 *     type="array",
 *     description="Products included in this package (only when loaded)",
 *
 *     @OA\Items(
 *       type="object",
 *
 *       @OA\Property(property="id", type="integer"),
 *       @OA\Property(property="title", type="string"),
 *       @OA\Property(property="product_type", type="string"),
 *       @OA\Property(property="original_price", type="number", format="float"),
 *       @OA\Property(property="discounted_price", type="number", format="float", nullable=true),
 *       @OA\Property(property="quantity", type="integer", description="Quantity of this product in the package")
 *     )
 *   ),
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
            'commerce_name' => $this->whenLoaded('commerce', fn () => $this->commerce?->name),
            'product_category_id' => $this->product_category_id,
            'category' => $this->whenLoaded('category', fn () => $this->category?->name),
            'title' => $this->title,
            'description' => $this->description,
            'product_type' => $this->product_type,
            'original_price' => $this->original_price,
            'discounted_price' => $this->discounted_price,
            'quantity_total' => $this->quantity_total,
            'quantity_available' => $this->quantity_available,
            'available_for_packaging' => $this->when(
                $this->product_type === Constant::PRODUCT_TYPE_SINGLE,
                fn () => $this->available_for_packaging
            ),
            'expires_at' => $this->expires_at,
            'photos' => $this->whenLoaded('photos', function () {
                return $this->photos->map(function ($photo) {
                    return new DocumentUploadResource($photo, ['product_id' => $this->id]);
                });
            }),
            'commerce_branches' => $this->whenLoaded('commerceBranches', function () {
                return $this->commerceBranches->map(fn ($branch) => [
                    'id' => $branch->id,
                    'name' => $branch->name,
                ]);
            }),
            'package_items' => $this->whenLoaded('packageItems', function () {
                return $this->packageItems->map(function ($item) {
                    return [
                        'id' => $item->id,
                        'title' => $item->title,
                        'product_type' => $item->product_type,
                        'original_price' => $item->original_price,
                        'discounted_price' => $item->discounted_price,
                        'quantity' => $item->pivot->quantity,
                    ];
                });
            }),
            'status' => $this->status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

        ];
    }
}
