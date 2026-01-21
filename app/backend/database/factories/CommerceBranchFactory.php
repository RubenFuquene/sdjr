<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Constants\Constant;
use App\Models\CommerceBranch;
use App\Models\Commerce;
use App\Models\Department;
use App\Models\City;
use App\Models\Neighborhood;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommerceBranchFactory extends Factory
{
    protected $model = CommerceBranch::class;

    public function definition(): array
    {
        return [
            'commerce_id' => Commerce::factory(),
            'department_id' => Department::factory(),
            'city_id' => City::factory(),
            'neighborhood_id' => Neighborhood::factory(),
            'name' => $this->faker->unique()->company.' Sucursal',
            'address' => $this->faker->streetAddress(),
            'latitude' => $this->faker->randomFloat(7, -90, 90),
            'longitude' => $this->faker->randomFloat(7, -180, 180),
            'phone' => $this->faker->optional()->numerify('3#########'),
            'email' => $this->faker->optional()->safeEmail(),
            'status' => $this->faker->randomElement([Constant::STATUS_ACTIVE, Constant::STATUS_INACTIVE]),
        ];
    }
}
