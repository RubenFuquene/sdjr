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
     * auto_adjusted_at / auto_adjusted_from alimentan el aviso de ajuste
     * automático de packs (SCRUM-361, Tarea 3): cuando la compra de un
     * componente deja un pack sobre-comprometido, el compromiso se ajusta en
     * silencio y estas dos columnas quedan pobladas para que el panel del
     * aliado muestre "bajó de X a Y por falta de stock". Solo aplican a
     * product_type=package; para individuales quedan siempre nulas.
     */
    public function up(): void
    {
        Schema::table('product_commerce_branch', function (Blueprint $table) {
            $table->timestamp('auto_adjusted_at')->nullable()->after('is_published');
            $table->integer('auto_adjusted_from')->nullable()->after('auto_adjusted_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_commerce_branch', function (Blueprint $table) {
            $table->dropColumn(['auto_adjusted_at', 'auto_adjusted_from']);
        });
    }
};
