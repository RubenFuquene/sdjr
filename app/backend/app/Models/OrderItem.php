<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *   schema="OrderItem",
 *   type="object",
 *   required={"id", "order_id", "product_id", "quantity", "unit_price"},
 *
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="order_id", type="integer"),
 *   @OA\Property(property="product_id", type="integer"),
 *   @OA\Property(property="quantity", type="integer"),
 *   @OA\Property(property="unit_price", type="number", format="float"),
 *   @OA\Property(property="subtotal", type="number", format="float")
 * )
 */
class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'id' => 'integer',
        'order_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'unit_price' => 'float',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Accessor para calcular subtotal
    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }
}
