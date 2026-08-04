<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Elimina products.quantity_total y products.quantity_available: el inventario
     * de todos los tipos de producto (single y package) vive por sede en
     * product_commerce_branch desde SCRUM-361 (Fase 2 de SCRUM-277). Sin backfill:
     * staging y dev son datos descartables, criterio ya establecido en la Fase 1.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['quantity_total', 'quantity_available']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->integer('quantity_total')->default(0);
            $table->integer('quantity_available')->default(0);
        });
    }
};
