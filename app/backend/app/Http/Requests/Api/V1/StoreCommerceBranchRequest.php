<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

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
