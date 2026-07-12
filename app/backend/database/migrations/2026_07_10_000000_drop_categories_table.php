<?php

declare(strict_types=1);

use App\Constants\Constant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Baja de `categories`: catálogo huérfano desde que `product_categories`
 * asumió la función real de categorizar productos (products.product_category_id).
 * No tiene FKs, no se consume en frontend. Incluye limpieza de los permisos
 * admin.params.categories.* (cascada por FK en role_has_permissions).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::table('permissions')->where('name', 'like', 'admin.params.categories.%')->delete();

        Schema::dropIfExists('categories');
    }

    public function down(): void
    {
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('icon', 100)->nullable();
            $table->char('status', 1)->default((string) Constant::STATUS_ACTIVE);
            $table->timestamps();
        });

        $guardName = 'sanctum';
        $now = now();

        DB::table('permissions')->insert([
            ['name' => 'admin.params.categories.index', 'guard_name' => $guardName, 'description' => 'Listar categorías', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'admin.params.categories.create', 'guard_name' => $guardName, 'description' => 'Crear categorías', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'admin.params.categories.show', 'guard_name' => $guardName, 'description' => 'Ver categorías', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'admin.params.categories.update', 'guard_name' => $guardName, 'description' => 'Permiso para actualizar categorías', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'admin.params.categories.delete', 'guard_name' => $guardName, 'description' => 'Permiso para eliminar categorías', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
};
