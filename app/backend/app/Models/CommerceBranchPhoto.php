<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class CommerceBranchPhoto
 *
 * @property int $id
 * @property int $commerce_branch_id
 * @property int|null $uploaded_by_id
 * @property string|null $upload_token
 * @property string|null $s3_etag
 * @property int|null $s3_object_size
 * @property string|null $s3_last_modified
 * @property int|null $replacement_of_id
 * @property int|null $version_of_id
 * @property int $version_number
 * @property string|null $expires_at
 * @property int $failed_attempts
 * @property string|null $photo_type
 * @property string|null $file_path
 * @property string|null $mime_type
 * @property string|null $uploaded_at
 */
class CommerceBranchPhoto extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'commerce_branch_id',
        'uploaded_by_id',
        'upload_token',
        's3_etag',
        's3_object_size',
        's3_last_modified',
        'replacement_of_id',
        'version_of_id',
        'version_number',
        'expires_at',
        'failed_attempts',
        'photo_type',
        'file_path',
        'mime_type',
        'uploaded_at',
    ];

    protected $casts = [
        'id' => 'integer',
        'commerce_branch_id' => 'integer',
        'uploaded_by_id' => 'integer',
        's3_object_size' => 'integer',
        'replacement_of_id' => 'integer',
        'version_of_id' => 'integer',
        'version_number' => 'integer',
        'failed_attempts' => 'integer',
        'expires_at' => 'datetime',
        'uploaded_at' => 'datetime',
        's3_last_modified' => 'datetime',
    ];

    /**
     * Relationship: belongs to CommerceBranch
     */
    public function branch()
    {
        return $this->belongsTo(CommerceBranch::class, 'commerce_branch_id');
    }

    /**
     * Relationship: belongs to User (uploaded_by)
     */
    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by_id');
    }
}
