<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="CommerceDocumentResource",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="document_type", type="string", example="ID_CARD"),
 *     @OA\Property(property="file_path", type="string", example="/uploads/doc.pdf"),
 *     @OA\Property(property="mime_type", type="string", example="application/pdf"),
 *     @OA\Property(property="verified", type="boolean", example=false),
 *     @OA\Property(property="uploaded_at", type="string", format="date-time"),
 * )
 */
class CommerceDocumentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'document_type' => $this->document_type,
            'mime_type' => $this->mime_type,
            'file_size_bytes' => $this->s3_object_size,
            'upload_status' => $this->upload_status,
            'version_number' => $this->version_number,
            'uploaded_at' => $this->uploaded_at?->toISOString(),
            'download' => [
                'mode' => 'url',
                'endpoint' => route('api.v1.document-download', ['id' => $this->id]),
            ],
            'file_path' => $this->file_path,
            'verified' => $this->verified,
            'uploaded_at' => $this->uploaded_at?->toISOString(),
            'verified_at' => $this->verified_at?->toISOString(),
        ];
    }
}
