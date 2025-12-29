<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\CommerceDocument;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommerceDocumentFactory extends Factory
{
    protected $model = CommerceDocument::class;

    public function definition(): array
    {
        return [
            'commerce_id' => null,
            'verified_by_id' => User::factory(),
            'uploaded_by_id' => User::factory(),
            'document_type' => $this->faker->randomElement(['ID_CARD', 'LICENSE', 'OTHER']),
            'file_path' => '/tmp/'.$this->faker->uuid().'.pdf',
            'mime_type' => 'application/pdf',
            'verified' => $this->faker->boolean(20),
            'uploaded_at' => now(),
            'verified_at' => null,
        ];
    }
}
