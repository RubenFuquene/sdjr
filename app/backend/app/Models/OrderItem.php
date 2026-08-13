<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\FiscalCode;
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
 *   @OA\Property(property="parent_package_id", type="integer", nullable=true, description="product_id del pack vendido, si esta línea es un componente suyo (SCRUM-366/367). Null en líneas normales."),
 *   @OA\Property(property="quantity", type="integer"),
 *   @OA\Property(property="unit_price", type="number", format="float"),
 *   @OA\Property(property="fiscal_code", type="string", nullable=true, description="Clasificación fiscal congelada al momento de la venta (SCRUM-376) — no la vigente del catálogo. Null en la línea padre de un pack y en órdenes previas a esta migración."),
 *   @OA\Property(property="vat_rate", type="number", format="float", nullable=true, description="Tarifa de IVA congelada en la venta."),
 *   @OA\Property(property="applies_inc", type="boolean", nullable=true, description="Impoconsumo congelado en la venta."),
 *   @OA\Property(property="inc_rate", type="number", format="float", nullable=true, description="Tarifa de impoconsumo congelada en la venta."),
 *   @OA\Property(property="subtotal", type="number", format="float")
 * )
 */
class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'parent_package_id',
        'quantity',
        'unit_price',
        'fiscal_code',
        'vat_rate',
        'applies_inc',
        'inc_rate',
    ];

    protected $casts = [
        'id' => 'integer',
        'order_id' => 'integer',
        'product_id' => 'integer',
        'parent_package_id' => 'integer',
        'quantity' => 'integer',
        'unit_price' => 'float',
        'fiscal_code' => FiscalCode::class,
        'vat_rate' => 'float',
        'applies_inc' => 'boolean',
        'inc_rate' => 'float',
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
