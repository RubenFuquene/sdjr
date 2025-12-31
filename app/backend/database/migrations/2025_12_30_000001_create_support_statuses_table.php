<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Constants\Constant;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('support_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 20)->unique();
            $table->string('color', 20);
            $table->char('status', 1)->default(Constant::STATUS_ACTIVE);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_statuses');
    }
};
