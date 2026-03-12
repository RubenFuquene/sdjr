<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\SanitizesTextAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ProductCategory
 *
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $status
 * @property int|null $establishment_type_id
 */
class ProductCategory extends Model
{
    use HasFactory, SanitizesTextAttributes, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'establishment_type_id',
        'name',
        'description',
        'status',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id' => 'integer',
        'name' => 'string',
        'description' => 'string',
        'status' => 'string',
        'establishment_type_id' => 'integer',
    ];

    /**
     * Get the establishment type that owns the product category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function establishmentType(): BelongsTo
    {
        return $this->belongsTo(EstablishmentType::class, 'establishment_type_id');
    }

    /**
     * Set the name attribute with sanitization and normalization.
     *
     * @param  string  $value
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $this->sanitizeText($value);
    }

    /**
     * Set the description attribute with sanitization and normalization.
     *
     * @param  string|null  $value
     */
    public function setDescriptionAttribute($value): void
    {
        $this->attributes['description'] = $this->sanitizeText($value);
    }
}
