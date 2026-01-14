<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

/**
 * ShowBankRequest
 *
 * @OA\Schema(
 *     schema="ShowBankRequest",
 *     type="object",
 *     description="Request schema for showing a bank. No body parameters required."
 * )
 */
class ShowBankRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.params.banks.index') ?? false;
    }

    public function rules(): array
    {
        return [];
    }
}
