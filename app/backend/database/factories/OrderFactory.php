<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Constants\Constant;
use App\Models\CommerceBranch;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'commerce_branch_id' => CommerceBranch::factory(),
            'total_price' => $this->faker->randomFloat(2, 10, 1000),
            'status' => $this->faker->randomElement([
                Constant::ORDER_STATUS_PENDING,
                Constant::ORDER_STATUS_CONFIRMED,
                Constant::ORDER_STATUS_PREPARING,
                Constant::ORDER_STATUS_READY,
                Constant::ORDER_STATUS_DELIVERED,
            ]),
        ];
    }

    // Estados específicos
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Constant::ORDER_STATUS_PENDING,
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Constant::ORDER_STATUS_CONFIRMED,
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Constant::ORDER_STATUS_DELIVERED,
        ]);
    }
}
