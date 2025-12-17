<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\SanitizesTextAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Neighborhood extends Model
{
    use HasFactory, SoftDeletes, SanitizesTextAttributes;

    protected $fillable = [
        'city_id',
        'name',
        'code',
        'status',
    ];

    protected $casts = [
        'id' => 'integer',
        'city_id' => 'integer',
        'name' => 'string',
        'code' => 'string',
        'status' => 'string',
    ];

    /**
     * Relationship: Neighborhood belongs to City
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Mutator: Normalize name
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $this->sanitizeText($value);
    }

    /**
     * Mutator: Normalize code (no capitalización, solo trim y upper)
     */
    public function setCodeAttribute($value): void
    {
        $this->attributes['code'] = strtoupper(trim($value));
    }
}
