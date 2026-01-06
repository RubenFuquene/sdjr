<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\SanitizesTextAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Class PqrsType
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class PqrsType extends Model
{
    use HasFactory, SanitizesTextAttributes;

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
     * Mutator for name attribute (normalizes text)
     *
     * @param  string  $value
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $this->sanitizeText($value);
    }

    /**
     * Mutator for code attribute (only trim, no capitalización)
     *
     * @param  string  $value
     */
    public function setCodeAttribute($value): void
    {
        $this->attributes['code'] = trim($value);
    }
}
