<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\SanitizesTextAttributes;

/**
 * Class SupportStatus
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $color
 * @property string $status
 * @property \Carbon\Carbon|null $created_at
 * @property \Carbon\Carbon|null $updated_at
 *
 * @method static \Illuminate\Database\Eloquent\Builder|SupportStatus query()
 */
class SupportStatus extends Model
{
    use HasFactory, SoftDeletes, SanitizesTextAttributes;

    protected $fillable = [
        'name',
        'code',
        'color',
        'status',
    ];

    protected $casts = [
        'name' => 'string',
        'code' => 'string',
        'color' => 'string',
        'status' => 'string',
    ];

    /**
     * Sanitize and normalize the name attribute.
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $this->sanitizeText($value);
    }

    /**
     * Sanitize and normalize the code attribute (solo trim, no capitalizar).
     */
    public function setCodeAttribute($value): void
    {
        $this->attributes['code'] = trim($value);
    }

    /**
     * Sanitize and normalize the color attribute (solo trim, no capitalizar).
     */
    public function setColorAttribute($value): void
    {
        $this->attributes['color'] = trim($value);
    }
}
