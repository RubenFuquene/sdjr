<?php

declare(strict_types=1);

namespace App\Models;

use App\Constants\Constant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\SanitizesTextAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property string $type
 * @property string $title
 * @property string $content
 * @property string $version
 * @property string $status
 * @property string|null $effective_date
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 * @property \Carbon\Carbon|null $deleted_at
 */
class LegalDocument extends Model
{
    use HasFactory, SoftDeletes, SanitizesTextAttributes;

    protected $fillable = [
        'type',
        'title',
        'content',
        'version',
        'status',
        'effective_date',
    ];

    protected $casts = [
        'effective_date' => 'date',
    ];

    /**
     * Sanitize and normalize the title attribute.
     */
    public function setTitleAttribute($value): void
    {
        $this->attributes['title'] = $this->sanitizeText($value);
    }

    /**
     * No sanitización para content (HTML), solo trim.
     */
    public function setContentAttribute($value): void
    {
        $this->attributes['content'] = is_string($value) ? trim($value) : $value;
    }
}
