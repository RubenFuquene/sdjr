<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Constants\Constant;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commerces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('department_id')->constrained('departments')->cascadeOnDelete();
            $table->foreignId('city_id')->constrained('cities')->cascadeOnDelete();
            $table->foreignId('neighborhood_id')->constrained('neighborhoods')->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('description', 500)->nullable();
            $table->string('tax_id', 30);
            $table->enum('tax_id_type', ['NIT', 'CC', 'PS', 'CE']);
            $table->string('address', 255);
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_verified')->default(false);            
            $table->timestamps();
            $table->softDeletes();
            $table->index(['owner_user_id', 'department_id', 'city_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerces');
    }
};
