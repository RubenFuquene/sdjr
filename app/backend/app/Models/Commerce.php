<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\SanitizesTextAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;


/**
 * @OA\Schema(
 *     schema="Commerce",
 *     title="Commerce",
 *     description="Commerce model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="owner_user_id", type="integer", example=1),
 *     @OA\Property(property="department_id", type="integer", example=1),
 *     @OA\Property(property="city_id", type="integer", example=1),
 *     @OA\Property(property="neighborhood_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Comercial S.A.S"),
 *     @OA\Property(property="description", type="string", example="Comercio de tecnología"),
 *     @OA\Property(property="tax_id", type="string", example="900123456"),
 *     @OA\Property(property="tax_id_type", type="string", example="NIT"),
 *     @OA\Property(property="address", type="string", example="Calle 123 #45-67"),
 *     @OA\Property(property="phone", type="string", example="3001234567"),
 *     @OA\Property(property="email", type="string", example="info@comercial.com"),
 *     @OA\Property(property="is_active", type="boolean", example=true),
 *     @OA\Property(property="is_verified", type="boolean", example=false),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-15T12:34:56Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-15T12:34:56Z"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", example=null)
 * )
 */
class Commerce extends Model
{
    use HasFactory, SoftDeletes, SanitizesTextAttributes;

    protected $fillable = [
        'owner_user_id',
        'department_id',
        'city_id',
        'neighborhood_id',
        'name',
        'description',
        'tax_id',
        'tax_id_type',
        'address',
        'phone',
        'email',
        'is_active',
        'is_verified',        
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function ownerUser() { return $this->belongsTo(User::class, 'owner_user_id'); }
    public function department() { return $this->belongsTo(Department::class); }
    public function city() { return $this->belongsTo(City::class); }
    public function neighborhood() { return $this->belongsTo(Neighborhood::class); }

    // Sanitización de campos de texto
    public function setNameAttribute($value) { $this->attributes['name'] = $this->sanitizeText($value); }
    public function setDescriptionAttribute($value) { $this->attributes['description'] = $this->sanitizeText($value); }
    public function setAddressAttribute($value) { $this->attributes['address'] = $this->sanitizeText($value); }
    public function setEmailAttribute($value) { $this->attributes['email'] = $this->sanitizeEmail($value); }
}
