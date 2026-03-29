<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\CommerceComment;
use App\Models\PriorityType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Factory for CommerceComment
 */
class CommerceCommentFactory extends Factory
{
    protected $model = CommerceComment::class;

    public function definition(): array
    {
        return [
            'commerce_id' => Commerce::factory(),
            'created_by' => User::factory(),
            'comment' => $this->faker->sentence(10),
            'priority_type_id' => PriorityType::factory(),
            'comment_type' => $this->faker->randomElement([
                Constant::COMMENT_TYPE_SUPPORT,
                Constant::COMMENT_TYPE_PRODUCT,
                Constant::COMMENT_TYPE_INFO,
                Constant::COMMENT_TYPE_VALIDATION,
            ]),
            'color' => $this->faker->safeColorName(),
            'status' => Constant::STATUS_ACTIVE,
        ];
    }
}
