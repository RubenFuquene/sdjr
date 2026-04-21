<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_package_items', function (Blueprint $table) {
            $table->unsignedInteger('quantity')->default(1)->after('product_package_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_package_items', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
};
