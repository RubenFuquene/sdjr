<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Autodeclaración del proveedor: si su comercio está obligado a emitir
     * factura electrónica. Determina si se exige la carga del formato 1876.
     */
    public function up(): void
    {
        Schema::table('commerces', function (Blueprint $table) {
            $table->boolean('electronic_invoicing_required')->default(false)->after('tax_id_type');
        });
    }

    public function down(): void
    {
        Schema::table('commerces', function (Blueprint $table) {
            $table->dropColumn('electronic_invoicing_required');
        });
    }
};
