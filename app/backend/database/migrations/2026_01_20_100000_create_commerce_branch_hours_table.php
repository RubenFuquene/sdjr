<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('commerce_branch_hours', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commerce_branch_id')->constrained('commerce_branches')->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week'); // 0=Domingo, 6=Sábado
            $table->time('open_time');
            $table->time('close_time');
            $table->string('note', 255)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_branch_hours');
    }
};
