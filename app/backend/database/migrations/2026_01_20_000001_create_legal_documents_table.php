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
        Schema::create('legal_documents', function (Blueprint $table) {
            $table->id();
            $table->enum('type', [
                Constant::LEGAL_DOCUMENT_TYPE_TERMS,
                Constant::LEGAL_DOCUMENT_TYPE_PRIVACY,
                Constant::LEGAL_DOCUMENT_TYPE_SERVICE_CONTRACT
            ]);
            $table->string('title');
            $table->longText('content'); // HTML structure
            $table->string('version', 32);
            $table->enum('status', [
                Constant::LEGAL_DOCUMENT_STATUS_DRAFT,
                Constant::LEGAL_DOCUMENT_STATUS_ACTIVE,
                Constant::LEGAL_DOCUMENT_STATUS_ARCHIVED
            ])->default(Constant::LEGAL_DOCUMENT_STATUS_DRAFT);
            $table->date('effective_date')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['type', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('legal_documents');
    }
};
