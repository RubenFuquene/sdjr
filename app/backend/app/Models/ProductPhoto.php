<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\SanitizesTextAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * Class ProductPhoto
 *
 * @property int $id
 * @property int $product_id
 * @property int|null $verified_by_id
 * @property int|null $uploaded_by_id
 * @property int|null $replacement_of_id
 * @property int|null $version_of_id
 * @property string|null $file_path
 * @property string|null $upload_token
 * @property string $upload_status
 * @property string|null $s3_etag
 * @property int|null $s3_object_size
 * @property Carbon|null $s3_last_modified
 * @property int $version_number
 * @property Carbon|null $expires_at
 * @property int $failed_attempts
 * @property string|null $mime_type
 * @property Carbon|null $uploaded_at
 * @property Carbon|null $verified_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 * @property Carbon|null $deleted_at
 */
class ProductPhoto extends Model
{
    use HasFactory, SanitizesTextAttributes, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'product_id',
        'verified_by_id',
        'uploaded_by_id',
        'replacement_of_id',
        'version_of_id',
        'file_path',
        'upload_token',
        'upload_status',
        's3_etag',
        's3_object_size',
        's3_last_modified',
        'version_number',
        'expires_at',
        'failed_attempts',
        'mime_type',
        'uploaded_at',
        'verified_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        's3_last_modified' => 'datetime',
        'expires_at' => 'datetime',
        'uploaded_at' => 'datetime',
        'verified_at' => 'datetime',
        's3_object_size' => 'integer',
        'version_number' => 'integer',
        'failed_attempts' => 'integer',
    ];

    /**
     * Get the product that owns the photo.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the user who verified the photo.
     */
    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by_id');
    }

    /**
     * Get the user who uploaded the photo.
     */
    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }

    /**
     * Get the previous version of this photo.
     */
    public function replacementOf()
    {
        return $this->belongsTo(ProductPhoto::class, 'replacement_of_id');
    }

    /**
     * Get the original version of this photo.
     */
    public function versionOf()
    {
        return $this->belongsTo(ProductPhoto::class, 'version_of_id');
    }

    /**
     * Mutator for file_path (sanitization).
     *
     * @param  string|null  $value
     */
    public function setFilePathAttribute($value): void
    {
        $this->attributes['file_path'] = $this->sanitizeText($value);
    }

    /**
     * Mutator for mime_type (sanitization).
     *
     * @param  string|null  $value
     */
    public function setMimeTypeAttribute($value): void
    {
        $this->attributes['mime_type'] = $this->sanitizeText($value);
    }
}
