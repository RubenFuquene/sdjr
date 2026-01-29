<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class ProductCommerceBranch
 *
 *
 * @property int $id
 * @property int $product_id
 * @property int $commerce_branch_id
 */
class ProductCommerceBranch extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'commerce_branch_id',
    ];

    protected $casts = [
        'id' => 'integer',
        'product_id' => 'integer',
        'commerce_branch_id' => 'integer',
    ];

    /**
     * Get the product for this branch item.
     */
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Get the commerce branch for this product item.
     */
    public function commerceBranch()
    {
        return $this->belongsTo(CommerceBranch::class);
    }
}
