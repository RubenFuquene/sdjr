<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class CommerceBranchHour
 *
 * @property int $id
 * @property int $commerce_branch_id
 * @property int $day_of_week
 * @property string $open_time
 * @property string $close_time
 * @property string|null $note
 */
class CommerceBranchHour extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'commerce_branch_id',
        'day_of_week',
        'open_time',
        'close_time',
        'note',
    ];

    protected $casts = [
        'id' => 'integer',
        'commerce_branch_id' => 'integer',
        'day_of_week' => 'integer',
        'open_time' => 'string',
        'close_time' => 'string',
        'note' => 'string',
    ];

    /**
     * Relationship: belongs to CommerceBranch
     */
    public function branch()
    {
        return $this->belongsTo(CommerceBranch::class, 'commerce_branch_id');
    }
}
