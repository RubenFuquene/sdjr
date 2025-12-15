<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\SanitizesTextAttributes;

class City extends Model
{
    use HasFactory, SanitizesTextAttributes;

    protected $fillable = [
        'department_id',
        'name',
        'status',
    ];

    /**
     * Get the department that owns the city.
     *
     * @return BelongsTo
     */
    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Sanitize name before saving.
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $this->sanitizeText($value);
    }
}
