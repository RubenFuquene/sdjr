<?php

declare(strict_types=1);

use App\Enums\FiscalCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SCRUM-376: congela la clasificación fiscal vigente del producto en el
 * momento de la venta, mismo patrón que unit_price (ya congelado aquí y
 * nunca releído de products). El futuro motor de FAN (SCRUM-252) debe leer
 * estas columnas, no las de products — products refleja lo vigente, esto
 * refleja lo que el comprador aceptó al pagar.
 *
 * Nullable siempre, por dos motivos distintos que no deben confundirse:
 * - Línea padre de un pack: los packs nunca tienen fiscal_code propio
 *   (SCRUM-362 D4), se facturan por sus líneas hijas de componente.
 * - Órdenes creadas antes de esta migración: sin backfill (pre-MVP, datos
 *   efímeros — decisión explícita, no una omisión).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->enum('fiscal_code', array_column(FiscalCode::cases(), 'value'))->nullable()->after('unit_price');
            $table->decimal('vat_rate', 5, 2)->nullable()->after('fiscal_code');
            $table->boolean('applies_inc')->nullable()->after('vat_rate');
            $table->decimal('inc_rate', 5, 2)->nullable()->after('applies_inc');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['fiscal_code', 'vat_rate', 'applies_inc', 'inc_rate']);
        });
    }
};
