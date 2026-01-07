<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\SanitizesTextAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class PriorityType
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $status
 * @property \Illuminate\Support\Carbon $created_at
 * @property \Illuminate\Support\Carbon $updated_at
 */
class PriorityType extends Model
{
    use HasFactory, SanitizesTextAttributes, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
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
        'code' => 'string',
        'status' => 'string',
    ];

    /**
     * Set the name attribute with sanitization and normalization.
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $this->sanitizeText($value);
    }

    /**
     * Set the code attribute (no normalization, only trim).
     */
    public function setCodeAttribute($value): void
    {
        $this->attributes['code'] = trim($value);
    }
}
