<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Creates the commerce_branch_users pivot table to manage
     * many-to-many relationships between users and commerce branches.
     * This allows multiple users with branch_leader role to be assigned
     * to multiple branches within a commerce.
     */
    public function up(): void
    {
        Schema::create('commerce_branch_users', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->foreignId('commerce_id')
                ->constrained('commerces')
                ->cascadeOnDelete();

            $table->foreignId('commerce_branch_id')
                ->constrained('commerce_branches')
                ->cascadeOnDelete();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index(['commerce_id', 'commerce_branch_id'], 'idx_commerce_branch');
            $table->index(['commerce_id', 'user_id'], 'idx_commerce_user');
            $table->index('user_id');

            // Unique constraint to prevent duplicate assignments
            $table->unique(['commerce_branch_id', 'user_id'], 'unique_branch_user');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('commerce_branch_users');
    }
};
