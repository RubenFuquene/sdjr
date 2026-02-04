<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Constants\Constant;
use App\Models\Commerce;
use App\Models\CommerceDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommerceDocumentFactory extends Factory
{
    protected $model = CommerceDocument::class;

    public function definition(): array
    {
        return [
            'commerce_id' => Commerce::factory(),
            'verified_by_id' => User::factory(),
            'uploaded_by_id' => User::factory(),
            'upload_token' => $this->faker->uuid(),
            'upload_status' => $this->faker->randomElement([Constant::UPLOAD_STATUS_PENDING, Constant::UPLOAD_STATUS_CONFIRMED, Constant::UPLOAD_STATUS_FAILED, Constant::UPLOAD_STATUS_ORPHANED]),
            's3_etag' => $this->faker->sha1(),
            's3_object_size' => $this->faker->numberBetween(10000, 5000000),
            's3_last_modified' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'replacement_of_id' => null,
            'version_of_id' => null,
            'version_number' => $this->faker->numberBetween(1, 10),
            'expires_at' => $this->faker->optional()->dateTimeBetween('now', '+2 years'),
            'failed_attempts' => $this->faker->numberBetween(0, 3),
            'document_type' => $this->faker->randomElement([Constant::DOCUMENT_TYPE_ID_CARD, Constant::DOCUMENT_TYPE_LICENSE, Constant::DOCUMENT_TYPE_OTHER, Constant::DOCUMENT_TYPE_CAMARA_COMERCIO, Constant::DOCUMENT_TYPE_RUT, Constant::DOCUMENT_TYPE_REGISTRATION]),
            'file_path' => '/tmp/'.$this->faker->uuid().'.pdf',
            'mime_type' => $this->faker->randomElement(Constant::ALLOWED_FILE_EXTENSIONS),
            'verified' => $this->faker->boolean(20),
            'uploaded_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'verified_at' => $this->faker->optional(0.2)->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
