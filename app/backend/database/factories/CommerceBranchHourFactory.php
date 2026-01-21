<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CommerceBranchHour;
use App\Models\CommerceBranch;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommerceBranchHourFactory extends Factory
{
    protected $model = CommerceBranchHour::class;

    public function definition(): array
    {
        return [
            'commerce_branch_id' => CommerceBranch::factory(),
            'day_of_week' => $this->faker->numberBetween(0, 6),
            'open_time' => $this->faker->time('H:i'),
            'close_time' => $this->faker->time('H:i'),
            'note' => $this->faker->optional()->sentence(),
        ];
    }
}
