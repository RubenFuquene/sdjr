<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CommercePayoutMethod;
use App\Models\Commerce;
use App\Models\Bank;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Constants\Constant;

class CommercePayoutMethodFactory extends Factory
{
    protected $model = CommercePayoutMethod::class;

    public function definition(): array
    {
        return [
            'commerce_id' => Commerce::factory(),
            'type' => Constant::PAYOUT_TYPE_BANK,
            'bank_id' => Bank::factory(),
            'account_type' => Constant::ACCOUNT_TYPE_SAVINGS,
            'account_number' => $this->faker->bankAccountNumber(),
            'owner_id' => User::factory(),
            'is_primary' => $this->faker->boolean(),
            'status' => Constant::STATUS_ACTIVE,
        ];
    }
}
