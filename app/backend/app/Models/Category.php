<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\SanitizesTextAttributes;

/**
 * @OA\Schema(
 *     schema="Category",
 *     title="Category",
 *     description="Category model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Alimentos"),
 *     @OA\Property(property="icon", type="string", example="icon.png"),
 *     @OA\Property(property="status", type="string", example="A"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Category extends Model
{
    use HasFactory, SanitizesTextAttributes;

    protected $fillable = [
        'name',
        'icon',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    /**
     * Sanitize name before saving.
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $this->sanitizeText($value);
    }
}
