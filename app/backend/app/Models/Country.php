<?php

namespace App\Models;

use App\Models\Traits\SanitizesTextAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory, SanitizesTextAttributes;

    protected $fillable = [
        'code',
        'name',
        'status',
    ];

    /**
     * Sanitize code before saving.
     */
    public function setCodeAttribute($value): void
    {
        $this->attributes['code'] = strtoupper(trim($value));
    }

    /**
     * Sanitize name before saving.
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $this->capitalizeText($value);
    }

    /**
     * Get the departments for the country.
     */
    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }
}
