<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\SanitizesTextAttributes;

class Country extends Model
{
    use HasFactory, SanitizesTextAttributes;

    protected $fillable = [
        'name',
        'status',
    ];

    /**
     * Sanitize name before saving.
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $this->sanitizeText($value);
    }

    /**
     * Get the departments for the country.
     *
     * @return HasMany
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }
}
