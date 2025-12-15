<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $action = $this->route()->getActionMethod();
        $permission = 'users.' . ($action === 'store' ? 'create' : 'update');
        return $this->user()?->can($permission) ?? false;
    }

    public function rules(): array
    {        
        $method = $this->method();


        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'password' => ['required_if:action,store', 'string', 'min:8', 'confirmed'],
            'status' => 'nullable|integer|in:0,1',
        ];

        if (in_array($method, ['POST'])) {
            $rules['email'] = ['required', 'email', 'unique:users,email'];
        } elseif (in_array($method, ['PUT', 'PATCH'])) {
            $rules['email'] = ['email'];
        }

        return $rules;
    }
}