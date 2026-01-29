<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\SanitizesTextAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ProductCategory
 *
 *
 * @property int $id
 * @property string $name
 * @property string|null $description
 * @property string $status
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
    ];

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
