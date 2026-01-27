<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Commerce;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Constants\Constant;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return [
            'commerce_id' => Commerce::factory(),
            'product_category_id' => ProductCategory::factory(),
            'title' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(8),
            'original_price' => $this->faker->randomFloat(2, 10, 1000),
            'discounted_price' => $this->faker->optional()->randomFloat(2, 5, 900),
            'quantity_total' => $this->faker->numberBetween(1, 100),
            'quantity_available' => $this->faker->numberBetween(0, 100),
            'expires_at' => $this->faker->optional()->dateTimeBetween('now', '+1 year'),
            'status' => Constant::STATUS_ACTIVE,
        ];
    }
}
