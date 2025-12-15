<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @OA\Schema(
 *     schema="AuditLogResource",
 *     title="Audit Log Resource",
 *     description="Audit log entry resource",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", nullable=true, example=5, description="ID del usuario autenticado o null"),
 *     @OA\Property(property="method", type="string", example="POST", description="Método HTTP usado"),
 *     @OA\Property(property="endpoint", type="string", example="/api/v1/users", description="Ruta solicitada"),
 *     @OA\Property(property="payload", type="object", description="Cuerpo de la petición (campos sensibles ofuscados)"),
 *     @OA\Property(property="response_code", type="integer", example=201, description="Código de respuesta HTTP"),
 *     @OA\Property(property="response_time", type="integer", example=123, description="Tiempo de respuesta en ms"),
 *     @OA\Property(property="ip_address", type="string", example="192.168.1.10", description="IP del cliente"),
 *     @OA\Property(property="user_agent", type="string", example="Mozilla/5.0", description="User agent del cliente"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-15T12:34:56Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-15T12:34:56Z")
 * )
 */
class AuditLogResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  Request  $request
     * @return array<string, mixed>
     */
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'method' => $this->method,
            'endpoint' => $this->endpoint,
            'payload' => $this->payload,
            'response_code' => $this->response_code,
            'response_time' => $this->response_time,
            'ip_address' => $this->ip_address,
            'user_agent' => $this->user_agent,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
