<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\SanitizesTextAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class CommerceBranch
 *
 * @property int $id
 * @property int $commerce_id
 * @property int $department_id
 * @property int $city_id
 * @property int $neighborhood_id
 * @property string $name
 * @property string $address
 * @property float|null $latitude
 * @property float|null $longitude
 * @property string|null $phone
 * @property string|null $email
 * @property bool $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class CommerceBranch extends Model
{
    use HasFactory, SanitizesTextAttributes, SoftDeletes;

    protected $fillable = [
        'commerce_id',
        'department_id',
        'city_id',
        'neighborhood_id',
        'name',
        'address',
        'latitude',
        'longitude',
        'phone',
        'email',
        'status',
    ];

    protected $casts = [
        'id' => 'integer',
        'commerce_id' => 'integer',
        'department_id' => 'integer',
        'city_id' => 'integer',
        'neighborhood_id' => 'integer',
        'name' => 'string',
        'address' => 'string',
        'latitude' => 'float',
        'longitude' => 'float',
        'phone' => 'string',
        'email' => 'string',
        'status' => 'boolean',
    ];

    /**
     * Relationship: belongs to Commerce
     */
    public function commerce()
    {
        return $this->belongsTo(Commerce::class);
    }

    /**
     * Relationship: belongs to Department
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Relationship: belongs to City
     */
    public function city()
    {
        return $this->belongsTo(City::class);
    }

    /**
     * Relationship: belongs to Neighborhood
     */
    public function neighborhood()
    {
        return $this->belongsTo(Neighborhood::class);
    }

    /**
     * Relationship: has many CommerceDocuments
     */
    public function commerceDocuments()
    {
        return $this->hasMany(CommerceDocument::class, 'commerce_branch_id');
    }

    /**
     * Relationship: has many CommerceBranchPhotos
     */
    public function commerceBranchPhotos()
    {
        return $this->hasMany(CommerceBranchPhoto::class, 'commerce_branch_id');
    }

    /**
     * Relationship: has many CommerceBranchHours
     */
    public function commerceBranchHours()
    {
        return $this->hasMany(CommerceBranchHour::class, 'commerce_branch_id');
    }

    /**
     * Mutator: Normalize name
     * @param string $value
     */
    public function setNameAttribute($value): void
    {
        $this->attributes['name'] = $this->sanitizeText($value);
    }

    /**
     * Mutator: Normalize address
     * @param string $value
     */
    public function setAddressAttribute($value): void
    {
        $this->attributes['address'] = $this->sanitizeText($value);
    }
}
