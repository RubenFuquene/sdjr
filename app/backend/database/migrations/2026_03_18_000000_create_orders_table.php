<?php

declare(strict_types=1);

use App\Constants\Constant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('commerce_branch_id')
                ->constrained('commerce_branches')
                ->cascadeOnDelete();
            $table->decimal('total_price', 10, 2);
            $table->enum('status', [
                Constant::ORDER_STATUS_PENDING,
                Constant::ORDER_STATUS_CONFIRMED,
                Constant::ORDER_STATUS_PREPARING,
                Constant::ORDER_STATUS_READY,
                Constant::ORDER_STATUS_DELIVERED,
                Constant::ORDER_STATUS_CANCELLED,
            ])->default(Constant::ORDER_STATUS_PENDING);
            $table->timestamps();
            $table->softDeletes();
            $table->index('user_id');
            $table->index('commerce_branch_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
