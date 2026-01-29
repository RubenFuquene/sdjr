<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Commerce;
use App\Models\LegalRepresentative;
use Illuminate\Database\Eloquent\Factories\Factory;

class LegalRepresentativeFactory extends Factory
{
    protected $model = LegalRepresentative::class;

    public function definition(): array
    {
        return [
            'commerce_id' => Commerce::factory(),
            'name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'document' => $this->faker->numerify('#########'),
            'document_type' => $this->faker->randomElement(['CC', 'CE', 'NIT', 'PAS']),
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'is_primary' => $this->faker->boolean(20),
            'status' => '1',
        ];
    }
}
