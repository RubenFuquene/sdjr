<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="CommerceBranchUser",
 *     title="Commerce Branch User",
 *     description="Pivot model for many-to-many relationship between users and commerce branches",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="commerce_id", type="integer", example=1),
 *     @OA\Property(property="commerce_branch_id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2026-05-11T10:00:00Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2026-05-11T10:00:00Z"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", example=null)
 * )
 */
class CommerceBranchUser extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'commerce_id',
        'commerce_branch_id',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get the commerce that owns the assignment.
     */
    public function commerce(): BelongsTo
    {
        return $this->belongsTo(Commerce::class);
    }

    /**
     * Get the commerce branch that owns the assignment.
     */
    public function commerceBranch(): BelongsTo
    {
        return $this->belongsTo(CommerceBranch::class);
    }

    /**
     * Get the user that owns the assignment.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
