<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Constants\Constant;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->word(),
            'icon' => $this->faker->imageUrl(64, 64, 'category'),
            'status' => $this->faker->randomElement([Constant::STATUS_ACTIVE, Constant::STATUS_INACTIVE]),
        ];
    }
}
