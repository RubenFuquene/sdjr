<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SCRUM-366/367: explota la venta de un pack en líneas de componente,
 * hijas informativas de la línea del pack (parent_package_id = product_id
 * del pack, no un id de order_item — no hay anidamiento posible porque un
 * pack no puede ser componente de otro).
 *
 * unique(order_id, product_id) se retira: rompía al comprar un pack junto
 * con uno de sus propios componentes sueltos en la misma orden (dos filas
 * legítimas con el mismo product_id: la del componente suelto y la hija del
 * pack). La garantía de "un producto no se repite en items sueltos" se
 * mueve a validación (StoreOrderRequest, regla distinct).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropUnique(['order_id', 'product_id']);

            $table->foreignId('parent_package_id')
                ->nullable()
                ->after('product_id')
                ->constrained('products')
                ->nullOnDelete();

            $table->index('parent_package_id');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('parent_package_id');
            $table->unique(['order_id', 'product_id']);
        });
    }
};
