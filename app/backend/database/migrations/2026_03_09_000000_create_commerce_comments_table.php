<?php

declare(strict_types=1);

use App\Constants\Constant;
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
        Schema::create('commerce_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commerce_id')->constrained('commerces')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('priority_type_id')->constrained('priority_types')->cascadeOnDelete();
            $table->string('comment', 500);
            $table->enum('comment_type', [
                Constant::COMMENT_TYPE_SUPPORT,
                Constant::COMMENT_TYPE_PRODUCT,
                Constant::COMMENT_TYPE_INFO,
                Constant::COMMENT_TYPE_VALIDATION])->default(Constant::COMMENT_TYPE_INFO);
            $table->string('color', 20)->nullable();
            $table->char('status', 1)->default(Constant::STATUS_ACTIVE);
            $table->timestamps();
            $table->softDeletes();

            $table->index(['created_by', 'commerce_id', 'status']);
            $table->index(['priority_type_id']);
            $table->index(['comment_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commerce_comments');
    }
};
