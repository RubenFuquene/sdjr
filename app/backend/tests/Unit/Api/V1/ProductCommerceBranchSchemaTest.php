<?php

declare(strict_types=1);

namespace Tests\Unit\Api\V1;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * SCRUM-277 Fase 1, Tarea 6.1: verifica el esquema resultante del pivote
 * producto-sede sobre una base limpia. Sin caso de backfill que probar
 * (staging/dev son data descartable — ver Parte 1, Tarea 1.1).
 */
class ProductCommerceBranchSchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_pivot_table_has_expected_columns(): void
    {
        $this->assertTrue(Schema::hasTable('product_commerce_branch'));

        $this->assertTrue(Schema::hasColumns('product_commerce_branch', [
            'id',
            'product_id',
            'commerce_branch_id',
            'quantity_available',
            'is_published',
            'created_at',
            'updated_at',
        ]));
    }

    /**
     * La tabla es deliberadamente mínima (ver Revisión de simplicidad, Parte
     * 1): sin quantity_total y sin deleted_at.
     */
    public function test_pivot_table_does_not_have_columns_removed_during_simplification(): void
    {
        $this->assertFalse(Schema::hasColumn('product_commerce_branch', 'quantity_total'));
        $this->assertFalse(Schema::hasColumn('product_commerce_branch', 'deleted_at'));
    }

    public function test_pivot_table_has_unique_index_on_product_and_branch(): void
    {
        $indexes = Schema::getIndexes('product_commerce_branch');

        $hasUniqueOnPair = collect($indexes)->contains(
            fn (array $index) => $index['unique']
                && $index['columns'] === ['product_id', 'commerce_branch_id']
        );

        $this->assertTrue($hasUniqueOnPair, 'Expected a unique index on (product_id, commerce_branch_id).');
    }
}
