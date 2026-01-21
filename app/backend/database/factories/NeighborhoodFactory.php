<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Constants\Constant;
use App\Models\City;
use App\Models\Neighborhood;
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
            'name' => $this->faker->unique()->streetName(),            
            'code' => $this->faker->unique()->bothify('NB####'),
            'status' => Constant::STATUS_ACTIVE,
        ];
    }
}
