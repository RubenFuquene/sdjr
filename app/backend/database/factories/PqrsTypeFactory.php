<?php
declare(strict_types=1);

namespace Database\Factories;

use App\Constants\Constant;
use App\Models\PqrsType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<PqrsType>
 */
class PqrsTypeFactory extends Factory
{
    protected $model = PqrsType::class;

    public function definition(): array
    {
        return [
            'name' => Str::ucfirst($this->faker->unique()->word()),
            'code' => strtoupper($this->faker->unique()->lexify('PQR????')),
            'status' => Constant::STATUS_ACTIVE,
        ];
    }
}
