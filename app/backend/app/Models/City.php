<?php

namespace App\Models;

use App\Models\Traits\SanitizesTextAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class City extends Model
{
    use HasFactory, SanitizesTextAttributes;

    protected $fillable = [
        'department_id',
        'code',
        'name',
        'status',
    ];

    /**
     * Get the department that owns the city.
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Get the neighborhoods for the city.
     */
    public function neighborhoods()
    {
        return $this->hasMany(Neighborhood::class);
    }

    /**
     * Sanitize name before saving.
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $this->sanitizeText($value);
    }

    /**
     * Sanitize code before saving.
     */
    public function setCodeAttribute($value): void
    {
        $this->attributes['code'] = strtoupper(trim($value));
    }
}
