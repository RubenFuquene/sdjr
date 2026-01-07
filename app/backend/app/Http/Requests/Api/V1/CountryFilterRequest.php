<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * CountryFilterRequest
 *
 * @OA\Schema(
 *     schema="CountryFilterRequest",
 *     type="object",
 *     required={},
 *
 *     @OA\Property(
 *         property="per_page",
 *         type="integer",
 *         minimum=1,
 *         maximum=100,
 *         description="Number of records per page (default: 15)",
 *         example=10
 *     ),
 *     @OA\Property(
 *         property="status",
 *         type="string",
 *         enum={"0", "1", "all"},
 *         description="Filter by status: 1 (active), 0 (inactive), all (all records)",
 *         example="1"
 *     )
 * )
 */
class CountryFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'per_page' => ['sometimes', 'integer', 'min:1', 'max:100'],
            'status' => ['sometimes', 'in:0,1,all'],
        ];
    }

    public function messages(): array
    {
        return [
            'per_page.integer' => "The 'per_page' field must be an integer. Example: per_page=10",
            'per_page.min' => "The 'per_page' field must be at least 1. Example: per_page=10",
            'per_page.max' => "The 'per_page' field must not be greater than 100. Example: per_page=10",
            'status.in' => "The 'status' field must be one of: 1 (active), 0 (inactive), all (all records). Example: status=1",
        ];
    }

    public function validatedPerPage(): int
    {
        return (int) ($this->input('per_page', 15));
    }

    public function validatedStatus(): string
    {
        return $this->input('status', 'all');
    }
}
