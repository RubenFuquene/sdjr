<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use App\Constants\Constant;

class RoleUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('roles.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'string', 'in:' . Constant::STATUS_ACTIVE . ',' . Constant::STATUS_INACTIVE],
        ];
    }
}
