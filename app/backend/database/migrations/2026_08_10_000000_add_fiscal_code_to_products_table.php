<?php

declare(strict_types=1);

use App\Enums\FiscalCode;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SCRUM-362: clasificación fiscal por producto. Nullable a nivel de BD,
     * igual que discounted_price — obligatorio solo para product_type=single,
     * exigido en el form request. Los packs no reciben fiscal_code propio
     * (D4 del plan): se facturan por sus líneas hijas, nunca por sí mismos.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->enum('fiscal_code', array_column(FiscalCode::cases(), 'value'))->nullable()->after('product_category_id');
            $table->decimal('vat_rate', 5, 2)->nullable()->after('fiscal_code');
            $table->boolean('applies_inc')->nullable()->after('vat_rate');
            $table->decimal('inc_rate', 5, 2)->nullable()->after('applies_inc');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['fiscal_code', 'vat_rate', 'applies_inc', 'inc_rate']);
        });
    }
};
