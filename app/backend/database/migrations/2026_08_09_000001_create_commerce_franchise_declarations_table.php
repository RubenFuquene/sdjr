<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Registro probatorio (OWASP A08) de la declaración de franquicia: el T&C
     * del proveedor (cláusula 5.3) establece que Ñapa no verifica esta
     * autodeclaración, así que ante una revisión de la DIAN la única defensa
     * es acreditar quién la hizo, cuándo y desde dónde. Append-only por
     * diseño — sin updated_at, nunca se edita ni se borra una fila existente.
     */
    public function up(): void
    {
        Schema::create('commerce_franchise_declarations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commerce_id')->constrained('commerces')->cascadeOnDelete();
            $table->boolean('operates_under_franchise');
            $table->foreignId('declared_by_user_id')->constrained('users')->cascadeOnDelete();
            $table->string('ip_address', 45);
            $table->string('user_agent', 500)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->index('commerce_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_franchise_declarations');
    }
};
