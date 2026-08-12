<?php

declare(strict_types=1);

namespace App\Models;

use App\Constants\Constant;
use App\Models\Traits\SanitizesTextAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="EstablishmentType",
 *     title="EstablishmentType",
 *     description="Establishment type model",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Restaurante"),
 *     @OA\Property(property="code", type="string", example="REST"),
 *     @OA\Property(property="status", type="string", example="1"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-15T12:34:56Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-15T12:34:56Z"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", example=null)
 * )
 */
class EstablishmentType extends Model
{
    use HasFactory, SanitizesTextAttributes, SoftDeletes;

    protected $fillable = [
        'name',
        'code',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Sanitización de campos de texto
    public function setNameAttribute($value)
    {
        $this->attributes['name'] = $this->sanitizeText($value);
    }

    public function setCodeAttribute($value)
    {
        $this->attributes['code'] = trim($value);
    }

    /**
     * Get the product categories for the establishment type.
     */
    public function productCategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class, 'establishment_type_id');
    }

    /**
     * SCRUM-365: solo RE/PA prestan servicio de expendio de comidas
     * (Art. 426 ET) y por tanto pueden declarar operación bajo franquicia.
     * Punto único reutilizado por la validación del comercio (SCRUM-362) y
     * por el resolver de códigos fiscales.
     */
    public function isFranchiseEligible(): bool
    {
        return in_array($this->code, Constant::FRANCHISE_ELIGIBLE_ESTABLISHMENT_TYPE_CODES, true);
    }
}
