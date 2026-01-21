<?php

declare(strict_types=1);

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="CommerceBranchPhotoResource",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="commerce_branch_id", type="integer", example=10),
 *     @OA\Property(property="uploaded_by_id", type="integer", example=2),
 *     @OA\Property(property="upload_token", type="string", example="uuid-1234"),
 *     @OA\Property(property="s3_etag", type="string", example="etag-abc"),
 *     @OA\Property(property="s3_object_size", type="integer", example=204800),
 *     @OA\Property(property="s3_last_modified", type="string", format="date-time", example="2026-01-20T10:00:00Z"),
 *     @OA\Property(property="replacement_of_id", type="integer", example=null),
 *     @OA\Property(property="version_of_id", type="integer", example=null),
 *     @OA\Property(property="version_number", type="integer", example=1),
 *     @OA\Property(property="expires_at", type="string", format="date-time", example="2026-02-01T00:00:00Z"),
 *     @OA\Property(property="failed_attempts", type="integer", example=0),
 *     @OA\Property(property="photo_type", type="string", example="EXTERIOR"),
 *     @OA\Property(property="file_path", type="string", example="/uploads/branch_10_photo1.jpg"),
 *     @OA\Property(property="mime_type", type="string", example="image/jpeg"),
 *     @OA\Property(property="uploaded_at", type="string", format="date-time", example="2026-01-20T10:00:00Z")
 * )
 */
class CommerceBranchPhotoResource extends JsonResource
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
            'commerce_branch_id' => $this->commerce_branch_id,
            'uploaded_by_id' => $this->uploaded_by_id,
            'upload_token' => $this->upload_token,
            's3_etag' => $this->s3_etag,
            's3_object_size' => $this->s3_object_size,
            's3_last_modified' => $this->s3_last_modified,
            'replacement_of_id' => $this->replacement_of_id,
            'version_of_id' => $this->version_of_id,
            'version_number' => $this->version_number,
            'expires_at' => $this->expires_at,
            'failed_attempts' => $this->failed_attempts,
            'photo_type' => $this->photo_type,
            'file_path' => $this->file_path,
            'mime_type' => $this->mime_type,
            'uploaded_at' => $this->uploaded_at,
        ];
    }
}
