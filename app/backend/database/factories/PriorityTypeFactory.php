<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Constants\Constant;
use App\Models\PriorityType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PriorityType>
 */
class PriorityTypeFactory extends Factory
{
    protected $model = PriorityType::class;

    public function definition(): array
    {
        $name = $this->faker->unique()->word();

        return [
            'name' => Str::ucfirst(Str::lower($name)),
            'code' => strtoupper(Str::random(4)),
            'status' => Constant::STATUS_ACTIVE,
        ];
    }
}
