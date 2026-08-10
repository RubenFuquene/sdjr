<?php

declare(strict_types=1);

use App\Enums\FiscalCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SCRUM-362: sugerencia de código fiscal por categoría comercial. Nunca
     * se aplica a ciegas — pasa siempre por FiscalCodeResolver (franquicia y
     * tipo de establecimiento del comercio pueden descartarla).
     */
    public function up(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->enum('default_fiscal_code', array_column(FiscalCode::cases(), 'value'))->nullable()->after('establishment_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('product_categories', function (Blueprint $table) {
            $table->dropColumn('default_fiscal_code');
        });
    }
};
