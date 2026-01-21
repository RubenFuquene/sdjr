<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *   schema="StoreCommerceBranchRequest",
 *   type="object",
 *   required={"commerce_branch", "commerce_branch_hours", "commerce_branch_photos"},
 *   @OA\Property(
 *     property="commerce_branch",
 *     type="object",
 *     required={"commerce_id", "department_id", "city_id", "neighborhood_id", "name", "address", "status"},
 *     @OA\Property(property="commerce_id", type="integer", example=1),
 *     @OA\Property(property="department_id", type="integer", example=1),
 *     @OA\Property(property="city_id", type="integer", example=1),
 *     @OA\Property(property="neighborhood_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Sucursal Principal"),
 *     @OA\Property(property="address", type="string", example="Calle 123 #45-67"),
 *     @OA\Property(property="latitude", type="number", format="float", example=4.6),
 *     @OA\Property(property="longitude", type="number", format="float", example=-74.1),
 *     @OA\Property(property="phone", type="string", example="3001234567"),
 *     @OA\Property(property="email", type="string", example="sucursal@comercio.com"),
 *     @OA\Property(property="status", type="boolean", example=true)
 *   ),
 *   @OA\Property(
 *     property="commerce_branch_hours",
 *     type="object",
 *     required={"day_of_week", "open_time", "close_time"},
 *     @OA\Property(property="day_of_week", type="integer", example=1, description="0=Domingo, 1=Lunes, ..."),
 *     @OA\Property(property="open_time", type="string", example="08:00"),
 *     @OA\Property(property="close_time", type="string", example="18:00"),
 *     @OA\Property(property="note", type="string", example="Horario normal")
 *   ),
 *   @OA\Property(
 *     property="commerce_branch_photos",
 *     type="object",
 *     required={"uploaded_by_id", "upload_token", "s3_etag", "s3_object_size", "s3_last_modified", "version_number", "failed_attempts", "file_path", "mime_type", "uploaded_at"},
 *     @OA\Property(property="uploaded_by_id", type="integer", example=1),
 *     @OA\Property(property="upload_token", type="string", example="token123"),
 *     @OA\Property(property="s3_etag", type="string", example="etag123"),
 *     @OA\Property(property="s3_object_size", type="integer", example=123456),
 *     @OA\Property(property="s3_last_modified", type="string", format="date-time", example="2026-01-20 10:00:00"),
 *     @OA\Property(property="replacement_of_id", type="integer", nullable=true, example=null),
 *     @OA\Property(property="version_of_id", type="integer", nullable=true, example=null),
 *     @OA\Property(property="version_number", type="integer", example=1),
 *     @OA\Property(property="expires_at", type="string", format="date-time", nullable=true, example=null),
 *     @OA\Property(property="failed_attempts", type="integer", example=0),
 *     @OA\Property(property="photo_type", type="string", example="front"),
 *     @OA\Property(property="file_path", type="string", example="/photos/branch1.jpg"),
 *     @OA\Property(property="mime_type", type="string", example="image/jpeg"),
 *     @OA\Property(property="uploaded_at", type="string", format="date-time", example="2026-01-20 10:00:00")
 *   )
 * )
 */
class StoreCommerceBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('provider.commerces.create') ?? false;
    }

    public function rules(): array
    {
        return [

            'commerce_branch.commerce_id' => ['required', 'integer', 'exists:commerces,id'],
            'commerce_branch.department_id' => ['required', 'integer', 'exists:departments,id'],
            'commerce_branch.city_id' => ['required', 'integer', 'exists:cities,id'],
            'commerce_branch.neighborhood_id' => ['required', 'integer', 'exists:neighborhoods,id'],
            'commerce_branch.name' => ['required', 'string', 'max:100'],
            'commerce_branch.address' => ['required', 'string', 'max:255'],
            'commerce_branch.latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'commerce_branch.longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'commerce_branch.phone' => ['nullable', 'string', 'max:20'],
            'commerce_branch.email' => ['nullable', 'email', 'max:100'],
            'commerce_branch.status' => ['boolean'],

            'commerce_branch_hours.day_of_week' => ['required', 'integer', 'between:0,6'],
            'commerce_branch_hours.open_time' => ['required', 'date_format:H:i'],
            'commerce_branch_hours.close_time' => ['required', 'date_format:H:i'],
            'commerce_branch_hours.note' => ['nullable'],

            'commerce_branch_photos.uploaded_by_id' => ['required', 'integer', 'exists:users,id'],
            'commerce_branch_photos.upload_token' => ['required', 'string', 'max:64'],
            'commerce_branch_photos.s3_etag' => ['required', 'string', 'max:255'],
            'commerce_branch_photos.s3_object_size' => ['required', 'integer'],
            'commerce_branch_photos.s3_last_modified' => ['required', 'date_format:Y-m-d H:i:s'],
            'commerce_branch_photos.replacement_of_id' => ['nullable', 'integer', 'exists:commerce_branch_photos,id'],
            'commerce_branch_photos.version_of_id' => ['nullable', 'integer', 'exists:commerce_branch_photos,id'],
            'commerce_branch_photos.version_number' => ['required', 'integer', 'min:1'],
            'commerce_branch_photos.expires_at' => ['nullable', 'date_format:Y-m-d H:i:s'],
            'commerce_branch_photos.failed_attempts' => ['required', 'integer', 'min:0'],
            'commerce_branch_photos.photo_type' => ['nullable', 'string', 'max:50'],
            'commerce_branch_photos.file_path' => ['required', 'string', 'max:255'],
            'commerce_branch_photos.mime_type' => ['required', 'string', 'max:100'],
            'commerce_branch_photos.uploaded_at' => ['required', 'date_format:Y-m-d H:i:s'],
        ];
    }
}
