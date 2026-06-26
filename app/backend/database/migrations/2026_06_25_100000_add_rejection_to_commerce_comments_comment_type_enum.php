<?php

declare(strict_types=1);

use App\Constants\Constant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add the 'RJ' (rejection) value to the commerce_comments.comment_type enum.
     * Required to persist rejection observations as RJ-typed comments (SCRUM-297).
     *
     * Uses the schema builder (portable across MySQL and the SQLite test database)
     * instead of a MySQL-specific raw ALTER statement.
     */
    public function up(): void
    {
        Schema::table('commerce_comments', function (Blueprint $table) {
            $table->enum('comment_type', [
                Constant::COMMENT_TYPE_SUPPORT,
                Constant::COMMENT_TYPE_PRODUCT,
                Constant::COMMENT_TYPE_INFO,
                Constant::COMMENT_TYPE_VALIDATION,
                Constant::COMMENT_TYPE_REJECTION,
            ])->default(Constant::COMMENT_TYPE_INFO)->change();
        });
    }

    /**
     * Restore the previous enum definition (without RJ).
     */
    public function down(): void
    {
        Schema::table('commerce_comments', function (Blueprint $table) {
            $table->enum('comment_type', [
                Constant::COMMENT_TYPE_SUPPORT,
                Constant::COMMENT_TYPE_PRODUCT,
                Constant::COMMENT_TYPE_INFO,
                Constant::COMMENT_TYPE_VALIDATION,
            ])->default(Constant::COMMENT_TYPE_INFO)->change();
        });
    }
};
