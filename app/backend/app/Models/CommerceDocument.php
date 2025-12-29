<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *     schema="CommerceDocument",
 *
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="commerce_id", type="integer", example=1),
 *     @OA\Property(property="verified_by_id", type="integer", example=1),
 *     @OA\Property(property="uploaded_by_id", type="integer", example=1),
 *     @OA\Property(property="document_type", type="string", example="ID_CARD"),
 *     @OA\Property(property="file_path", type="string", example="/uploads/doc.pdf"),
 *     @OA\Property(property="mime_type", type="string", example="application/pdf"),
 *     @OA\Property(property="verified", type="boolean", example=false),
 *     @OA\Property(property="uploaded_at", type="string", format="date-time"),
 *     @OA\Property(property="verified_at", type="string", format="date-time", nullable=true)
 * )
 */
class CommerceDocument extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'commerce_id',
        'verified_by_id',
        'uploaded_by_id',
        'document_type',
        'file_path',
        'mime_type',
        'verified',
        'uploaded_at',
        'verified_at',
    ];

    protected $casts = [
        'verified' => 'boolean',
        'uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    public function commerce()
    {
        return $this->belongsTo(Commerce::class);
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }
}
