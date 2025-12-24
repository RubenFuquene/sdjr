<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *     schema="AuditLog",
 *     title="Audit Log",
 *     description="Audit log entry",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", nullable=true, example=5),
 *     @OA\Property(property="method", type="string", example="POST"),
 *     @OA\Property(property="endpoint", type="string", example="/api/v1/countries"),
 *     @OA\Property(property="payload", type="string", example="{...}"),
 *     @OA\Property(property="response_code", type="integer", example=200),
 *     @OA\Property(property="response_time", type="integer", example=123),
 *     @OA\Property(property="ip_address", type="string", example="127.0.0.1"),
 *     @OA\Property(property="user_agent", type="string", example="PostmanRuntime/7.32.2"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'method',
        'endpoint',
        'payload',
        'response_code',
        'response_time',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'response_code' => 'integer',
        'response_time' => 'integer',
    ];
}
