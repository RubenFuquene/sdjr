<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *   schema="DocumentUploadResource",
 *   type="object",
 *   title="Document Upload Resource",
 *   description="Recurso que representa un documento subido por comercio.",
 *   required={"id", "commerce_id", "document_type", "upload_token", "upload_status", "file_path", "mime_type", "expires_at", "uploaded_by_id", "failed_attempts", "created_at", "updated_at"},
 *   @OA\Property(property="id", type="integer", example=1),
 *   @OA\Property(property="commerce_id", type="integer", example=123),
 *   @OA\Property(property="document_type", type="string", example="factura"),
 *   @OA\Property(property="upload_token", type="string", example="uuid-token"),
 *   @OA\Property(property="upload_status", type="string", example="pending"),
 *   @OA\Property(property="file_path", type="string", example="documents/commerce_123/uuid-token/factura.pdf"),
 *   @OA\Property(property="mime_type", type="string", example="application/pdf"),
 *   @OA\Property(property="expires_at", type="string", format="date-time", example="2026-01-21T12:00:00Z"),
 *   @OA\Property(property="uploaded_by_id", type="integer", example=5),
 *   @OA\Property(property="failed_attempts", type="integer", example=0),
 *   @OA\Property(property="created_at", type="string", format="date-time", example="2026-01-21T11:00:00Z"),
 *   @OA\Property(property="updated_at", type="string", format="date-time", example="2026-01-21T11:05:00Z")
 * )
 */
class DocumentUploadResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'commerce_id' => $this->commerce_id,
            'document_type' => $this->document_type,            
            'upload_status' => $this->upload_status,
            's3_etag' => $this->s3_etag,
            's3_object_size' => $this->s3_object_size,
            'file_path' => $this->file_path,
            'uploaded_by_id' => $this->uploaded_by_id,            
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
