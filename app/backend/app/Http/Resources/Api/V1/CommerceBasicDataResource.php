<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="CommerceBasicDataResource",
 *
 *     @OA\Property(property="commerce", ref="#/components/schemas/CommerceResource"),
 *     @OA\Property(property="legal_representatives", type="array", @OA\Items(ref="#/components/schemas/LegalRepresentativeResource")),
 *     @OA\Property(property="commerce_documents", type="array", @OA\Items(ref="#/components/schemas/CommerceDocumentResource"))
 * )
 */
class CommerceBasicDataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request): array
    {
        $commerce = $this->resource;

        return [
            'commerce' => new CommerceResource($commerce),
            'legal_representatives' => LegalRepresentativeResource::collection($this->whenLoaded('legalRepresentatives')),
            'commerce_documents' => CommerceDocumentResource::collection($this->whenLoaded('commerceDocuments')),
        ];
    }
}
