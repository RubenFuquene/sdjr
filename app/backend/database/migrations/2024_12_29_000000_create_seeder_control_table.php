<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seeder_control', function (Blueprint $table) {
            $table->id();
            $table->string('seeder_name')->unique();
            $table->string('version')->default('1.0');
            $table->timestamp('executed_at');
            $table->json('metadata')->nullable(); // Para guardar info adicional
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seeder_control');
    }
};
