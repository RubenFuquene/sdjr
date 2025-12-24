<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Neighborhood;
use App\Models\City;
use App\Constants\Constant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Neighborhood>
 */
class NeighborhoodFactory extends Factory
{
    protected $model = Neighborhood::class;

    public function definition(): array
    {
        return [
            'city_id' => City::factory(),
            'name' => $this->faker->streetName(),
            'code' => strtoupper($this->faker->unique()->bothify('NB####')),
            'status' => Constant::STATUS_ACTIVE,
        ];
    }
}
