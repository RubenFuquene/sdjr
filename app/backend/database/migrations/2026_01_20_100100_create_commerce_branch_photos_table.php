<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commerce_branch_photos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commerce_branch_id')->constrained('commerce_branches')->cascadeOnDelete();
            $table->foreignId('uploaded_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('upload_token', 255)->unique()->nullable();
            $table->string('s3_etag', 255)->nullable();
            $table->bigInteger('s3_object_size')->nullable();
            $table->dateTime('s3_last_modified')->nullable();
            $table->foreignId('replacement_of_id')->nullable()->constrained('commerce_branch_photos')->nullOnDelete();
            $table->foreignId('version_of_id')->nullable()->constrained('commerce_branch_photos')->nullOnDelete();
            $table->unsignedInteger('version_number')->default(1);
            $table->dateTime('expires_at')->nullable();
            $table->unsignedInteger('failed_attempts')->default(0);
            $table->string('photo_type', 50)->nullable();
            $table->string('file_path')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index('upload_token');
            $table->index('photo_type');
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commerce_branch_photos');
    }
};
