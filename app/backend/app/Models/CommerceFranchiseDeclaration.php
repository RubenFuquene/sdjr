<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *     schema="CommerceFranchiseDeclaration",
 *     title="CommerceFranchiseDeclaration",
 *     description="Registro probatorio, append-only, de una declaración de franquicia (SCRUM-365)",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="commerce_id", type="integer", example=1),
 *     @OA\Property(property="operates_under_franchise", type="boolean", example=true),
 *     @OA\Property(property="declared_by_user_id", type="integer", example=5),
 *     @OA\Property(property="ip_address", type="string", example="192.168.1.10"),
 *     @OA\Property(property="user_agent", type="string", nullable=true, example="Mozilla/5.0"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2026-08-09T12:34:56Z")
 * )
 */
class CommerceFranchiseDeclaration extends Model
{
    /**
     * Append-only: nunca se edita una declaración existente, solo se agrega
     * una nueva. Sin updated_at.
     */
    const UPDATED_AT = null;

    protected $fillable = [
        'commerce_id',
        'operates_under_franchise',
        'declared_by_user_id',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'commerce_id' => 'integer',
        'operates_under_franchise' => 'boolean',
        'declared_by_user_id' => 'integer',
        'created_at' => 'datetime',
    ];

    public function commerce(): BelongsTo
    {
        return $this->belongsTo(Commerce::class);
    }

    public function declaredByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'declared_by_user_id');
    }
}
