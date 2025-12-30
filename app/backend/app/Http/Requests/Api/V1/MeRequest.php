<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class MeRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Permite solo usuarios autenticados
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [];
    }
}
