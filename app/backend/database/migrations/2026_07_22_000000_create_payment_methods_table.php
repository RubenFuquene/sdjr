<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Métodos de pago tokenizados del comprador (entidad "Payment Method" del ER).
 * NO confundir con commerce_payout_methods (liquidación al aliado).
 * Solo se guardan datos tokenizados/enmascarados: nunca el PAN completo.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->string('provider'); // pasarela dueña del token (ej. "fake", futura real)
            $table->string('token')->nullable();
            $table->string('last4', 4)->nullable();
            $table->string('brand')->nullable();
            $table->unsignedTinyInteger('exp_month')->nullable();
            $table->unsignedSmallInteger('exp_year')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
