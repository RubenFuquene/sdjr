<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\EstablishmentType;
use Illuminate\Database\Eloquent\Factories\Factory;

class EstablishmentTypeFactory extends Factory
{
    protected $model = EstablishmentType::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->word(),
            'code' => strtoupper($this->faker->unique()->lexify('????')),
            'status' => '1',
        ];
    }
}
