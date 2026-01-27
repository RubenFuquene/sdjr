<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\SanitizesTextAttributes;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Product
 *
 * @package App\Models
 *
 * @property int $id
 * @property int $commerce_id
 * @property int $product_category_id
 * @property string $title
 * @property string|null $description
 * @property float $original_price
 * @property float|null $discounted_price
 * @property int $quantity_total
 * @property int $quantity_available
 * @property string|null $expires_at
 * @property string $product_type
 * @property string $status
 */
class Product extends Model
{
    use HasFactory, SanitizesTextAttributes, SoftDeletes;

    protected $fillable = [
        'commerce_id',
        'product_category_id',
        'title',
        'description',
        'product_type',
        'original_price',
        'discounted_price',
        'quantity_total',
        'quantity_available',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'id' => 'integer',
        'commerce_id' => 'integer',
        'product_category_id' => 'integer',
        'title' => 'string',
        'description' => 'string',
        'product_type' => 'string',
        'original_price' => 'float',
        'discounted_price' => 'float',
        'quantity_total' => 'integer',
        'quantity_available' => 'integer',
        'expires_at' => 'datetime',
        'status' => 'string',
    ];

    /**
     * Set the title attribute with sanitization and normalization.
     *
     * @param string $value
     * @return void
     */
    public function setTitleAttribute($value): void
    {
        $this->attributes['title'] = $this->capitalizeText($value);
    }

    /**
     * Set the description attribute with sanitization and normalization.
     *
     * @param string|null $value
     * @return void
     */
    public function setDescriptionAttribute($value): void
    {
        $this->attributes['description'] = $this->sanitizeText($value);
    }

    /**
     * Get the product category.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    /**
     * Get the commerce.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function commerce()
    {
        return $this->belongsTo(Commerce::class, 'commerce_id');
    }

    /**
     * The commerce branches that belong to the product.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function commerceBranches()
    {
        return $this->belongsToMany(CommerceBranch::class, 'product_commerce_branch', 'product_id', 'commerce_branch_id');
    }

    /**
     * The products that belong to the package.
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function packageItems()
    {
        return $this->belongsToMany(Product::class, 'product_package_items', 'product_package_id', 'product_id');
    }

}
