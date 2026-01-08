<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int $id
 * @property int $commerce_id
 * @property string $type
 * @property int|null $bank_id
 * @property string|null $account_type
 * @property string|null $account_number
 * @property int $owner_id
 * @property bool $is_primary
 * @property string $status
 */
class CommercePayoutMethod extends Model
{
    use HasFactory;

    protected $table = 'commerce_payout_methods';

    protected $fillable = [
        'commerce_id',
        'type',
        'bank_id',
        'account_type',
        'account_number',
        'owner_id',
        'is_primary',
        'status',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'bank_id' => 'integer',
        'owner_id' => 'integer',
        'commerce_id' => 'integer',
    ];

    /**
     * Commerce relation.
     */
    public function commerce(): BelongsTo
    {
        return $this->belongsTo(Commerce::class);
    }

    /**
     * Bank relation.
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    /**
     * Owner relation.
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }
}
