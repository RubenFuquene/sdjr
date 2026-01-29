<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\City;
use App\Models\Commerce;
use App\Models\Department;
use App\Models\Neighborhood;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommerceFactory extends Factory
{
    protected $model = Commerce::class;

    public function definition(): array
    {
        return [
            'owner_user_id' => User::factory(),
            'department_id' => Department::factory(),
            'city_id' => City::factory(),
            'neighborhood_id' => Neighborhood::factory(),
            'name' => $this->faker->company(),
            'description' => $this->faker->catchPhrase(),
            'tax_id' => $this->faker->numerify('#########'),
            'tax_id_type' => $this->faker->randomElement(['NIT', 'CC', 'CE']),
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->unique()->safeEmail(),
            'is_active' => $this->faker->boolean(90),
            'is_verified' => $this->faker->boolean(10),
        ];
    }
}
