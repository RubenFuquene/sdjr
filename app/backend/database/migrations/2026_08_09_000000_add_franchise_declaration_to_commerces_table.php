<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * SCRUM-362/365: opera_bajo_franquicia determina si el comercio puede
     * usar el código fiscal de Impoconsumo (Art. 426 ET). establishment_type_id
     * pasa a obligatorio: sin él, el resolver de códigos fiscales no tiene
     * entrada. Pre-MVP, sin data real en ningún ambiente — sin backfill.
     */
    public function up(): void
    {
        Schema::table('commerces', function (Blueprint $table) {
            $table->boolean('operates_under_franchise')->default(false)->after('establishment_type_id');
        });

        Schema::table('commerces', function (Blueprint $table) {
            $table->dropForeign(['establishment_type_id']);
        });

        Schema::table('commerces', function (Blueprint $table) {
            $table->unsignedBigInteger('establishment_type_id')->nullable(false)->change();
        });

        Schema::table('commerces', function (Blueprint $table) {
            $table->foreign('establishment_type_id')->references('id')->on('establishment_types')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('commerces', function (Blueprint $table) {
            $table->dropForeign(['establishment_type_id']);
        });

        Schema::table('commerces', function (Blueprint $table) {
            $table->unsignedBigInteger('establishment_type_id')->nullable()->change();
        });

        Schema::table('commerces', function (Blueprint $table) {
            $table->foreign('establishment_type_id')->references('id')->on('establishment_types')->cascadeOnDelete();
            $table->dropColumn('operates_under_franchise');
        });
    }
};
