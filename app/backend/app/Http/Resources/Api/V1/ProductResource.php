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
 *   @OA\Property(property="commerce_name", type="string", nullable=true, description="Nombre del comercio propietario (solo si la relacion commerce esta cargada)"),
 *   @OA\Property(property="product_category_id", type="integer"),
 *   @OA\Property(property="category", type="string", nullable=true, description="Nombre de la categoria (solo si la relacion category esta cargada)"),
 *   @OA\Property(property="fiscal_code", type="string", nullable=true, example="iva_19_general", description="SCRUM-362: null solo en packs — nunca llevan clasificación propia, se facturan por sus líneas hijas"),
 *   @OA\Property(property="vat_rate", type="number", format="float", nullable=true, description="Derivado de fiscal_code — nunca aceptado del cliente"),
 *   @OA\Property(property="applies_inc", type="boolean", nullable=true, description="Derivado de fiscal_code — nunca aceptado del cliente"),
 *   @OA\Property(property="inc_rate", type="number", format="float", nullable=true, description="Derivado de fiscal_code — nunca aceptado del cliente"),
 *   @OA\Property(property="title", type="string"),
 *   @OA\Property(property="description", type="string", nullable=true),
 *   @OA\Property(property="product_type", type="string", enum={"single","package"}),
 *   @OA\Property(property="original_price", type="number", format="float"),
 *   @OA\Property(property="discounted_price", type="number", format="float", nullable=true),
 *   @OA\Property(property="expires_at", type="string", format="date-time", nullable=true),
 *   @OA\Property(property="photos", type="array", @OA\Items(ref="#/components/schemas/DocumentUploadResource")),
 *   @OA\Property(
 *     property="commerce_branches",
 *     type="array",
 *     description="Sucursales asignadas al producto, con su inventario y estado de publicación por sede (solo si la relacion commerceBranches esta cargada). SCRUM-277/361.",
 *
 *     @OA\Items(
 *       type="object",
 *
 *       @OA\Property(property="id", type="integer"),
 *       @OA\Property(property="name", type="string"),
 *       @OA\Property(property="quantity_available", type="integer", nullable=true, description="Para product_type=single: unidades disponibles en esta sede. Para product_type=package: packs comprometidos en esta sede (SCRUM-361)."),
 *       @OA\Property(property="is_published", type="boolean", nullable=true, description="Si el producto es visible a clientes en esta sede"),
 *       @OA\Property(property="auto_adjusted_at", type="string", format="date-time", nullable=true, description="Solo packs: cuándo se ajustó solo el compromiso por falta de stock de un componente (SCRUM-361)"),
 *       @OA\Property(property="auto_adjusted_from", type="integer", nullable=true, description="Solo packs: cantidad comprometida antes del ajuste automático"),
 *       @OA\Property(property="available_for_packaging", type="integer", nullable=true, description="Solo product_type=single, solo presente cuando el backend lo precalculó (ProductService::getByCommerce): stock que queda libre para comprometer en packs en esta sede (SCRUM-361)")
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
            'fiscal_code' => $this->fiscal_code?->value,
            'vat_rate' => $this->vat_rate,
            'applies_inc' => $this->applies_inc,
            'inc_rate' => $this->inc_rate,
            'title' => $this->title,
            'description' => $this->description,
            'product_type' => $this->product_type,
            'original_price' => $this->original_price,
            'discounted_price' => $this->discounted_price,
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
                    'quantity_available' => $branch->pivot->quantity_available,
                    'is_published' => (bool) $branch->pivot->is_published,
                    'auto_adjusted_at' => $branch->pivot->auto_adjusted_at,
                    'auto_adjusted_from' => $branch->pivot->auto_adjusted_from,
                    'available_for_packaging' => $branch->pivot->getAttribute('available_for_packaging'),
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
