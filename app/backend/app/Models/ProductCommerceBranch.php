<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Class ProductCommerceBranch
 *
 * Pivote producto-sede: fuente de verdad única del inventario y del estado
 * de publicación por sede, para ambos tipos de producto (SCRUM-277 Fase 1 +
 * SCRUM-361 Fase 2). Extiende Pivot (no Model) porque Product::commerceBranches()
 * lo declara vía ->using() — Laravel requiere que el modelo pivote provisto
 * a using() extienda Pivot para poder hidratarlo (fromRawAttributes()) al
 * resolver la relación.
 *
 * quantity_available tiene dos lecturas según product_type de la fila
 * padre — es el único punto del modelo donde el significado de una columna
 * depende del tipo, y por eso vale la pena dejarlo explícito aquí:
 *   - single:  unidades físicas disponibles en esa sede.
 *   - package: cuántos packs se comprometen a ofrecer en esa sede (no es
 *              stock físico; es un compromiso sincronizado con la capacidad
 *              real de los componentes — ver PackageAvailabilityCalculator).
 *
 * auto_adjusted_at / auto_adjusted_from solo aplican a product_type=package:
 * quedan pobladas cuando el compromiso se ajustó solo porque un cliente
 * agotó stock de un componente por compra (SCRUM-361, Tarea 3). Para
 * individuales permanecen siempre nulas.
 *
 * @property int $id
 * @property int $product_id
 * @property int $commerce_branch_id
 * @property int $quantity_available
 * @property bool $is_published
 * @property \Illuminate\Support\Carbon|null $auto_adjusted_at
 * @property int|null $auto_adjusted_from
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
        'auto_adjusted_at',
        'auto_adjusted_from',
    ];

    protected $casts = [
        'id' => 'integer',
        'product_id' => 'integer',
        'commerce_branch_id' => 'integer',
        'quantity_available' => 'integer',
        'is_published' => 'boolean',
        'auto_adjusted_at' => 'datetime',
        'auto_adjusted_from' => 'integer',
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
