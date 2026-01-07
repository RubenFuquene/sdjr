<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

/**
 * AuditLogRequest
 *
 * @OA\Schema(
 *     schema="AuditLogRequest",
 *     type="object",
 *
 *     @OA\Property(property="user_id", type="integer", nullable=true, example=1, description="ID del usuario autenticado"),
 *     @OA\Property(property="method", type="string", maxLength=10, example="GET", description="Método HTTP usado en la petición"),
 *     @OA\Property(property="endpoint", type="string", maxLength=255, example="/api/v1/users", description="Endpoint solicitado"),
 *     @OA\Property(property="payload", type="string", nullable=true, example="{\"key\":\"value\"}", description="Cuerpo de la petición (JSON serializado)"),
 *     @OA\Property(property="response_code", type="integer", example=200, description="Código de respuesta HTTP"),
 *     @OA\Property(property="response_time", type="integer", example=123, description="Tiempo de respuesta en milisegundos"),
 *     @OA\Property(property="ip_address", type="string", maxLength=45, nullable=true, example="192.168.1.1", description="Dirección IP del cliente"),
 *     @OA\Property(property="user_agent", type="string", maxLength=255, nullable=true, example="Mozilla/5.0", description="User Agent del cliente")
 * )
 */
class AuditLogRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only allow if user is authenticated
        return Auth::check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'method' => ['required', 'string', 'max:10'],
            'endpoint' => ['required', 'string', 'max:255'],
            'payload' => ['nullable', 'string'],
            'response_code' => ['required', 'integer'],
            'response_time' => ['required', 'integer'],
            'ip_address' => ['nullable', 'string', 'max:45'],
            'user_agent' => ['nullable', 'string', 'max:255'],
        ];
    }
}
