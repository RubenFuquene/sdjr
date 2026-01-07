<?php

declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;


/**
 * Class AuditLogRequest
 *
 * @OA\Schema(
 *     schema="AuditLogRequest",
 *     required={"method", "endpoint", "response_code", "response_time"},
 *     @OA\Property(property="user_id", type="integer", nullable=true, example=1, description="User ID (nullable)"),
 *     @OA\Property(property="method", type="string", maxLength=10, example="POST", description="HTTP method"),
 *     @OA\Property(property="endpoint", type="string", maxLength=255, example="/api/v1/resource", description="Requested endpoint"),
 *     @OA\Property(property="payload", type="string", nullable=true, example="{key:value}", description="Request payload (nullable, JSON string)"),
 *     @OA\Property(property="response_code", type="integer", example=200, description="HTTP response code"),
 *     @OA\Property(property="response_time", type="integer", example=123, description="Response time in ms"),
 *     @OA\Property(property="ip_address", type="string", maxLength=45, nullable=true, example="192.168.1.1", description="Client IP address (nullable)"),
 *     @OA\Property(property="user_agent", type="string", maxLength=255, nullable=true, example="Mozilla/5.0", description="User agent (nullable)")
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
