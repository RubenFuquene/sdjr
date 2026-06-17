<?php

declare(strict_types=1);

namespace App\Models;

use App\Constants\Constant;
use App\Models\Traits\SanitizesTextAttributes;
use App\Services\PackageAvailabilityCalculator;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Class Product
 *
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
     * @param  string  $value
     */
    public function setTitleAttribute($value): void
    {
        $this->attributes['title'] = $this->capitalizeText($value);
    }

    /**
     * Set the description attribute with sanitization and normalization.
     *
     * @param  string|null  $value
     */
    public function setDescriptionAttribute($value): void
    {
        $this->attributes['description'] = $this->sanitizeText($value);
    }

    /**
     * Get the available quantity, considering pending orders.
     */
    public function getQuantityAvailableAttribute(): int
    {
        // Suma la cantidad solicitada en órdenes abiertas
        $reservedQuantity = (int) OrderItem::whereHas('order', function ($query) {
            $query->whereIn('status', [Constant::ORDER_STATUS_PENDING, Constant::ORDER_STATUS_PREPARING, Constant::ORDER_STATUS_READY]);
        })->where('product_id', $this->id)->sum('quantity');

        return (int) $this->attributes['quantity_available'] - intval($reservedQuantity);
    }

    /**
     * Get the stock of this product still available to be committed to packages,
     * after subtracting the stock already committed by packages that include it.
     */
    public function getAvailableForPackagingAttribute(): int
    {
        return app(PackageAvailabilityCalculator::class)->availableForPackaging($this);
    }

    /**
     * Get the product category.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function category()
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    /**
     * Get the commerce.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function commerce()
    {
        return $this->belongsTo(Commerce::class, 'commerce_id');
    }

    /**
     * The commerce branches that belong to the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function commerceBranches()
    {
        return $this->belongsToMany(CommerceBranch::class, 'product_commerce_branch', 'product_id', 'commerce_branch_id');
    }

    /**
     * The products that belong to the package.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function packageItems()
    {
        return $this->belongsToMany(Product::class, 'product_package_items', 'product_package_id', 'product_id')
            ->withPivot('quantity');
    }

    /**
     * The products that belong to the package (inverse relationship).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function package()
    {
        return $this->belongsToMany(Product::class, 'product_package_items', 'product_id', 'product_package_id')
            ->withPivot('quantity');
    }

    /**
     * Get the photos for the product.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function photos()
    {
        return $this->hasMany(ProductPhoto::class, 'product_id');
    }
}
