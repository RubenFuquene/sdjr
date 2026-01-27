<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Constants\Constant;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commerce_id')->constrained('commerces')->cascadeOnDelete();
            $table->foreignId('product_category_id')->constrained('product_categories')->cascadeOnDelete();
            $table->string('title', 100);
            $table->string('description', 255)->nullable();
            $table->enum('product_type', [Constant::PRODUCT_TYPE_SINGLE, Constant::PRODUCT_TYPE_PACKAGE])->default(Constant::PRODUCT_TYPE_SINGLE);
            $table->decimal('original_price', 10, 2);
            $table->decimal('discounted_price', 10, 2)->nullable();
            $table->integer('quantity_total')->default(0);
            $table->integer('quantity_available')->default(0);
            $table->timestamp('expires_at')->nullable();
            $table->char('status', 1)->default(Constant::STATUS_ACTIVE);
            $table->timestamps();
            $table->softDeletes();

            $table->index('commerce_id');
            $table->index('product_category_id');
            $table->index('title');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
