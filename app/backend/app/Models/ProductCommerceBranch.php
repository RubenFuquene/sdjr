<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class ProductCommerceBranch
 *
 * Pivote producto-sede: fuente de verdad del inventario y del estado de
 * publicación por sede (SCRUM-277 Fase 1). Extiende Pivot (no Model) porque
 * Product::commerceBranches() lo declara vía ->using() — Laravel requiere
 * que el modelo pivote provisto a using() extienda Pivot para poder
 * hidratarlo (fromRawAttributes()) al resolver la relación.
 *
 * @property int $id
 * @property int $product_id
 * @property int $commerce_branch_id
 * @property int $quantity_available
 * @property bool $is_published
 */
class ProductCommerceBranch extends Pivot
{
    use HasFactory;

    /**
     * La tabla sí tiene id autoincremental propio, a diferencia del pivote
     * "plano" que Pivot asume por defecto ($incrementing = false).
     */
    public $incrementing = true;

    protected $fillable = [
        'product_id',
        'commerce_branch_id',
        'quantity_available',
        'is_published',
    ];

    protected $casts = [
        'id' => 'integer',
        'product_id' => 'integer',
        'commerce_branch_id' => 'integer',
        'quantity_available' => 'integer',
        'is_published' => 'boolean',
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
