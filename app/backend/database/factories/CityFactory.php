<?php

namespace Database\Factories;

use App\Constants\Constant;
use App\Models\Department;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\City>
 */
class CityFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'department_id' => Department::factory(),
            'name' => $this->faker->city(),
            'code' => strtoupper($this->faker->unique()->bothify('??####')),
            'status' => $this->faker->randomElement([Constant::STATUS_ACTIVE, Constant::STATUS_INACTIVE]),
        ];
    }
}
