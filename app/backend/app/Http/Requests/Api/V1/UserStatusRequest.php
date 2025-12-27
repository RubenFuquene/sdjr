<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UserStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('admin.users.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'status' => ['required', 'in:0,1'],
        ];
    }
}
