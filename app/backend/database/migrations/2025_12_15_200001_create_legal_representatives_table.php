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
        Schema::create('legal_representatives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commerce_id')->constrained('commerces')->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('last_name', 255);
            $table->string('document', 30);
            $table->enum('document_type', ['CC', 'CE', 'NIT', 'PAS'])->default('CC');
            $table->string('email', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->boolean('is_primary')->default(false);
            $table->char('status', 1)->default(Constant::STATUS_ACTIVE);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['commerce_id', 'document']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_representatives');
    }
};
