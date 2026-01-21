<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CommerceBranch;
use App\Models\CommerceBranchPhoto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommerceBranchPhotoFactory extends Factory
{
    protected $model = CommerceBranchPhoto::class;

    public function definition(): array
    {
        return [
            'commerce_branch_id' => CommerceBranch::factory(),
            'uploaded_by_id' => User::factory(),
            'upload_token' => $this->faker->uuid(),
            's3_etag' => $this->faker->sha1(),
            's3_object_size' => $this->faker->numberBetween(10000, 5000000),
            's3_last_modified' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'replacement_of_id' => null,
            'version_of_id' => null,
            'version_number' => $this->faker->numberBetween(1, 10),
            'expires_at' => $this->faker->optional()->dateTimeBetween('now', '+2 years'),
            'failed_attempts' => $this->faker->numberBetween(0, 3),
            'photo_type' => $this->faker->randomElement(['EXTERIOR', 'INTERIOR', 'PRODUCT']),
            'file_path' => '/tmp/'.$this->faker->uuid().'.jpg',
            'mime_type' => 'image/jpeg',
            'uploaded_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
