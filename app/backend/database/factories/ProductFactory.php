<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Constants\Constant;
use App\Enums\FiscalCode;
use App\Models\Commerce;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        // SCRUM-362: IVA general es compatible con los tres tipos de
        // establecimiento (a diferencia de INC/licor, exclusivos de un
        // subconjunto) — evita acoplar este factory a un tipo específico.
        $fiscalCode = FiscalCode::Vat19General;

        return [
            'commerce_id' => Commerce::factory(),
            'product_category_id' => ProductCategory::factory(),
            'fiscal_code' => $fiscalCode,
            'vat_rate' => $fiscalCode->vatRate(),
            'applies_inc' => $fiscalCode->appliesInc(),
            'inc_rate' => $fiscalCode->incRate(),
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(8),
            'original_price' => $this->faker->randomFloat(2, 10, 1000),
            'discounted_price' => $this->faker->optional()->randomFloat(2, 5, 900),
            'expires_at' => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
            'status' => Constant::STATUS_ACTIVE,
        ];
    }
}
