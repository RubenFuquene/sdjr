<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\SupportStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Constants\Constant;

class SupportStatusFactory extends Factory
{
    protected $model = SupportStatus::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'code' => $this->faker->unique()->lexify('STATUS????'),
            'color' => $this->faker->safeColorName(),
            'status' => Constant::STATUS_ACTIVE,
        ];
    }
}
