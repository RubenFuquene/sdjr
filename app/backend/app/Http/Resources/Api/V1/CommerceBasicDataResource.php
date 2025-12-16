<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Api\V1\LegalRepresentativeResource;

class CommerceBasicDataResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'commerce' => new CommerceResource($this->resource),
            'legal_representatives' => LegalRepresentativeResource::collection($this->whenLoaded('legalRepresentatives')),
            'commerce_documents' => CommerceDocumentResource::collection($this->whenLoaded('commerceDocuments')),
        ];
    }
}
