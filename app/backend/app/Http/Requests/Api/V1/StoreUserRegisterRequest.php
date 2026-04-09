<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRegisterRequest extends FormRequest
{
    /**
     * Determina si el usuario está autorizado para hacer esta petición.
     */
    public function authorize(): bool
    {
        // Registro público, no requiere autorización
        return true;
    }

    public function prepareForValidation(): void
    {
        if ($this->is('provider/register')){
            $this->merge([
                'role' => 'provider', // El rol se asignará automáticamente en el controlador
            ]);
        } elseif ($this->is('customer/register')) {
            $this->merge([
                'role' => 'user', // El rol se asignará automáticamente en el controlador
            ]);
        }         
    }

    /**
     * Reglas de validación para el registro de proveedor.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ];
    }
}
