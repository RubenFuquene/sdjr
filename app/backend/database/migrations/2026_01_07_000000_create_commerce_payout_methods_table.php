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
        Schema::create('commerce_payout_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commerce_id')->constrained()->cascadeOnDelete();
            $table->enum('type', [
                Constant::PAYOUT_TYPE_BANK,
                Constant::PAYOUT_TYPE_PAYPAL,
                Constant::PAYOUT_TYPE_CRYPTO,
            ]);
            $table->foreignId('bank_id')->nullable()->constrained('banks')->nullOnDelete();
            $table->enum('account_type', [
                Constant::ACCOUNT_TYPE_SAVINGS,
                Constant::ACCOUNT_TYPE_CHECKING,
                Constant::ACCOUNT_TYPE_OTHER,
            ])->nullable();
            $table->string('account_number', 100)->nullable();
            $table->foreignId('owner_id')->constrained('users')->cascadeOnDelete();
            $table->boolean('is_primary')->default(false);
            $table->char('status', 1)->default(Constant::STATUS_ACTIVE);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_payout_methods');
    }
};
