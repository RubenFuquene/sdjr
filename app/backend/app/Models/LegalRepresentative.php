<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\SanitizesTextAttributes;

/**
 * @OA\Schema(
 *     schema="LegalRepresentative",
 *     title="LegalRepresentative",
 *     description="Legal representative model",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="commerce_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Juan"),
 *     @OA\Property(property="last_name", type="string", example="Pérez"),
 *     @OA\Property(property="document", type="string", example="1234567890"),
 *     @OA\Property(property="document_type", type="string", enum={"CC","CE","NIT","PAS"}, example="CC"),
 *     @OA\Property(property="email", type="string", example="juan.perez@example.com"),
 *     @OA\Property(property="phone", type="string", example="3001234567"),
 *     @OA\Property(property="is_primary", type="boolean", example=true),
 *     @OA\Property(property="status", type="string", example="1"),
 *     @OA\Property(property="created_at", type="string", format="date-time", example="2025-12-15T12:34:56Z"),
 *     @OA\Property(property="updated_at", type="string", format="date-time", example="2025-12-15T12:34:56Z"),
 *     @OA\Property(property="deleted_at", type="string", format="date-time", example=null)
 * )
 */
class LegalRepresentative extends Model
{
    use HasFactory, SoftDeletes, SanitizesTextAttributes;

    protected $fillable = [
        'commerce_id',
        'name',
        'last_name',
        'document',
        'document_type',
        'email',
        'phone',
        'is_primary',
        'status',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function commerce() { return $this->belongsTo(Commerce::class); }

    // Sanitización de campos de texto
    public function setNameAttribute($value) { $this->attributes['name'] = $this->capitalizeText($value); }
    public function setLastNameAttribute($value) { $this->attributes['last_name'] = $this->capitalizeText($value); }
    public function setEmailAttribute($value) { $this->attributes['email'] = $this->sanitizeEmail($value); }
}
