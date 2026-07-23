<?php

declare(strict_types=1);

use App\Enums\TransactionStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Cargos/pagos del comprador (entidad "Transactions" del ER).
 * Desacoplado del estado de la orden: una orden pending puede acumular
 * transacciones rejected/failed; solo una approved la confirma.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();
            $table->foreignId('payment_method_id')
                ->nullable()
                ->constrained('payment_methods')
                ->nullOnDelete();
            $table->string('provider'); // gateway que procesó (ej. "fake")
            $table->string('external_id')->nullable(); // referencia del gateway (el ER lo tipaba boolean: corregido a string)
            $table->enum('status', array_column(TransactionStatus::cases(), 'value'))
                ->default(TransactionStatus::Initiated->value);
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('COP');
            $table->json('payload')->nullable(); // respuesta cruda del gateway, solo trazabilidad interna
            $table->timestamps();
            $table->softDeletes();
            $table->index('order_id');
            $table->index('status');
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
