<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * DeleteBankRequest
 *
 * @OA\Schema(
 *     schema="DeleteBankRequest",
 *     type="object",
 *     description="Request schema for deleting a bank. No body parameters required."
 * )
 */
class DeleteBankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.params.banks.delete') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
