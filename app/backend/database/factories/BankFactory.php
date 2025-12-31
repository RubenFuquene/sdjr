<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Bank;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Constants\Constant;

class BankFactory extends Factory
{
    protected $model = Bank::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->company(),
            'code' => $this->faker->unique()->swiftBicNumber(),
            'status' => Constant::STATUS_ACTIVE,
        ];
    }
}
