<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SCRUM-277 Fase 1: el pivote producto-sede pasa a ser la fuente de verdad
 * del inventario y del estado de publicación por sede (antes vivían en
 * products, sin noción de sede).
 *
 * Sin backfill: staging y dev contienen solo data de prueba, descartable —
 * se recrea la tabla en vez de migrar filas existentes.
 *
 * Deliberadamente mínimo — esta tabla resuelve la ASIGNACIÓN de un producto a
 * una sede, no la gestión de inventario:
 * - Sin softDeletes: quitar una sede de un producto no necesita conservar su
 *   inventario histórico, y tenerlo obligaba a restaurar filas soft-eliminadas
 *   para no chocar contra el índice único al reasignar esa misma sede.
 * - Una sola columna de cantidad: quantity_available es el stock disponible en
 *   esa sede. No se guarda además un quantity_total para poder derivar
 *   "cuántas se vendieron": eso convertiría al pivote en un libro de
 *   inventario a medias — sin fechas, ni motivos, ni movimientos — que no
 *   sirve para auditar y sí obliga a mantener dos números coherentes.
 *   La trazabilidad real de inventario va en su propio sistema (ticket aparte).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('product_commerce_branch');

        Schema::create('product_commerce_branch', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('commerce_branch_id')->constrained('commerce_branches')->cascadeOnDelete();
            $table->unsignedInteger('quantity_available')->default(0);
            $table->boolean('is_published')->default(false);
            $table->timestamps();

            $table->unique(['product_id', 'commerce_branch_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_commerce_branch');

        Schema::create('product_commerce_branch', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('commerce_branch_id')->constrained('commerce_branches')->cascadeOnDelete();
        });
    }
};
