<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductPhoto;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ProductPhoto>
 */
class ProductPhotoFactory extends Factory
{
    protected $model = ProductPhoto::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'verified_by_id' => User::factory(),
            'uploaded_by_id' => User::factory(),
            'replacement_of_id' => null,
            'version_of_id' => null,
            'file_path' => $this->faker->imageUrl(),
            'upload_token' => Str::random(32),
            'upload_status' => 'pending',
            's3_etag' => Str::random(16),
            's3_object_size' => $this->faker->numberBetween(10000, 1000000),
            's3_last_modified' => $this->faker->dateTime(),
            'version_number' => 1,
            'expires_at' => $this->faker->dateTimeBetween('+1 hour', '+2 hours'),
            'failed_attempts' => 0,
            'mime_type' => 'image/jpeg',
            'uploaded_at' => now(),
            'verified_at' => null,
        ];
    }
}
