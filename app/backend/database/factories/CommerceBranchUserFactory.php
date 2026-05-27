<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\CommerceBranchUser;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommerceBranchUserFactory extends Factory
{
    protected $model = CommerceBranchUser::class;

    public function definition(): array
    {
        return [
            'commerce_id' => Commerce::factory(),
            'commerce_branch_id' => CommerceBranch::factory(),
            'user_id' => User::factory(),
        ];
    }
}
