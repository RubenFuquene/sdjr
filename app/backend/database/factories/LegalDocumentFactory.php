<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Constants\Constant;
use App\Models\LegalDocument;
use Illuminate\Database\Eloquent\Factories\Factory;

class LegalDocumentFactory extends Factory
{
    protected $model = LegalDocument::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement([
            Constant::LEGAL_DOCUMENT_TYPE_TERMS,
            Constant::LEGAL_DOCUMENT_TYPE_PRIVACY,
            Constant::LEGAL_DOCUMENT_TYPE_SERVICE_CONTRACT,
        ]);
        $status = $this->faker->randomElement([
            Constant::LEGAL_DOCUMENT_STATUS_DRAFT,
            Constant::LEGAL_DOCUMENT_STATUS_ACTIVE,
            Constant::LEGAL_DOCUMENT_STATUS_ARCHIVED,
        ]);

        return [
            'type' => $type,
            'title' => $this->faker->sentence(4),
            'content' => '<h1>'.$this->faker->sentence(3).'</h1><p>'.$this->faker->paragraph(3).'</p>',
            'version' => 'v'.$this->faker->randomDigitNotNull(),
            'status' => $status,
            'effective_date' => $this->faker->date(),
        ];
    }
}
