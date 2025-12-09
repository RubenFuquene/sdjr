<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasUuids, HasFactory;

    protected $fillable = [
        'name',
        'status',
    ];

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
