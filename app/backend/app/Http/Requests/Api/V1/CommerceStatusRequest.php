<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use App\Constants\Constant;

class CommerceStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('commerces.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'is_active' => ['required', 'integer', 'in:' . Constant::STATUS_ACTIVE . ',' . Constant::STATUS_INACTIVE],
        ];
    }
}
