<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FiscalCode;
use App\Models\Traits\SanitizesTextAttributes;
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
 * @property string|null $expires_at
 * @property string $product_type
 * @property string $status
 *
 * fiscal_code/vat_rate/applies_inc/inc_rate son nullable a nivel de BD pero
 * obligatorios para product_type=single (SCRUM-362, exigido en el form
 * request). Un pack NUNCA los recibe propios: se factura por sus líneas
 * hijas (parent_package_id en OrderItem), cada una con su propio código y
 * base ya prorrateada — facturar también al pack cobraría dos veces.
 */
class Product extends Model
{
    use HasFactory, SanitizesTextAttributes, SoftDeletes;

    protected $fillable = [
        'commerce_id',
        'product_category_id',
        'fiscal_code',
        'vat_rate',
        'applies_inc',
        'inc_rate',
        'title',
        'description',
        'product_type',
        'original_price',
        'discounted_price',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'id' => 'integer',
        'commerce_id' => 'integer',
        'product_category_id' => 'integer',
        'fiscal_code' => FiscalCode::class,
        'vat_rate' => 'float',
        'applies_inc' => 'boolean',
        'inc_rate' => 'float',
        'title' => 'string',
        'description' => 'string',
        'product_type' => 'string',
        'original_price' => 'float',
        'discounted_price' => 'float',
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
     * The commerce branches that belong to the product, with per-branch
     * inventory and publication state (SCRUM-277 Fase 1).
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function commerceBranches()
    {
        return $this->belongsToMany(CommerceBranch::class, 'product_commerce_branch', 'product_id', 'commerce_branch_id')
            ->using(ProductCommerceBranch::class)
            ->withPivot(['id', 'quantity_available', 'is_published', 'auto_adjusted_at', 'auto_adjusted_from'])
            ->withTimestamps();
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

    /**
     * Precio de venta vigente: el que la app le muestra al cliente.
     *
     * Punto único reutilizado por la creación de órdenes y por el
     * prorrateo de packs (SCRUM-366) — antes cada consumidor resolvía el
     * precio por su cuenta y terminaban divergiendo.
     */
    public function currentSalePrice(): float
    {
        return (float) ($this->discounted_price ?? $this->original_price);
    }
}
