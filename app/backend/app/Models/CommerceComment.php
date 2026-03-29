<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\SanitizesTextAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Model for commerce comments
 *
 * @property int $id
 * @property int $commerce_id
 * @property string $comment
 * @property string|null $priority
 * @property string $comment_type // Enum: SUPPORT, INFO, VALIDATION
 * @property string|null $color
 * @property string $status
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class CommerceComment extends Model
{
    use HasFactory, SanitizesTextAttributes, SoftDeletes;

    protected $table = 'commerce_comments';

    protected $fillable = [
        'commerce_id',
        'created_by',
        'comment',
        'priority_type_id',
        'comment_type',
        'color',
        'status',
    ];

    protected $casts = [
        'commerce_id' => 'integer',
        'created_by' => 'integer',
        'comment' => 'string',
        'priority_type_id' => 'integer',
        'comment_type' => 'string',
        'color' => 'string',
        'status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relationship: PriorityType
     */
    public function priorityType(): BelongsTo
    {
        return $this->belongsTo(PriorityType::class, 'priority_type_id');
    }

    /**
     * Relationship: User (creator)
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relationship: Commerce
     */
    public function commerce(): BelongsTo
    {
        return $this->belongsTo(Commerce::class);
    }

    /**
     * Mutator for comment field (sanitize and normalize)
     *
     * @param  string  $value
     */
    public function setCommentAttribute($value): void
    {
        $this->attributes['comment'] = $this->sanitizeText($value);
    }

    /**
     * Mutator for color field (sanitize and normalize)
     *
     * @param  string|null  $value
     */
    public function setColorAttribute($value): void
    {
        $this->attributes['color'] = $this->sanitizeText($value);
    }
}
