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
        Schema::create('product_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('verified_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('uploaded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('replacement_of_id')->nullable()->constrained('product_photos')->nullOnDelete();
            $table->foreignId('version_of_id')->nullable()->constrained('product_photos')->nullOnDelete();
            $table->string('file_path')->nullable();
            $table->string('presigned_url', 2048)->nullable();
            $table->string('upload_token', 255)->unique()->nullable();
            $table->enum('upload_status', [
                Constant::UPLOAD_STATUS_PENDING,
                Constant::UPLOAD_STATUS_CONFIRMED,
                Constant::UPLOAD_STATUS_FAILED,
                Constant::UPLOAD_STATUS_ORPHANED,
            ])->default(Constant::UPLOAD_STATUS_PENDING);
            $table->string('s3_etag', 255)->nullable();
            $table->bigInteger('s3_object_size')->nullable();
            $table->dateTime('s3_last_modified')->nullable();
            $table->unsignedInteger('version_number')->default(1);
            $table->dateTime('expires_at')->nullable();
            $table->unsignedInteger('failed_attempts')->default(0);
            $table->string('mime_type')->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('upload_status');
            $table->index('expires_at');
            $table->index(['product_id', 'upload_status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_photos');
    }
};
