<?php

declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\SanitizesTextAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use HasFactory, SanitizesTextAttributes, SoftDeletes;

    protected $fillable = [
        'user_id',
        'commerce_branch_id',
        'total_price',
        'status',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'commerce_branch_id' => 'integer',
        'total_price' => 'float',
    ];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commerceBranch(): BelongsTo
    {
        return $this->belongsTo(CommerceBranch::class);
    }

    /**
     * Líneas "padre" de la orden — lo que se vendió y factura. Excluye
     * deliberadamente las líneas hijas de componente (SCRUM-366/367): son
     * informativas, no dinero ni stock propios, y casi todo consumidor
     * existente (descuento de stock, correo, disponibilidad) asume que cada
     * fila de items() es una venta real. Si algún día un consumidor
     * necesita ver también las hijas, debe pedirlas explícitamente vía
     * allItems() — nunca aquí, para no reintroducir en silencio el doble
     * descuento de stock que esto evita (ver PackagePriceProrationService).
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class)->whereNull('parent_package_id');
    }

    /**
     * Todas las líneas de la orden, incluidas las hijas de componente.
     * Uso previsto: pruebas del invariante contable y una futura vista de
     * recibo desglosado — no reemplaza a items() en ningún consumidor
     * existente.
     */
    public function allItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    // Accessor para obtener el comercio desde la sucursal
    public function getCommerceAttribute()
    {
        return $this->commerceBranch?->commerce;
    }
}
