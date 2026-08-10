<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Constants\Constant;
use App\Models\City;
use App\Models\Commerce;
use App\Models\Department;
use App\Models\EstablishmentType;
use App\Models\Neighborhood;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CommerceFactory extends Factory
{
    protected $model = Commerce::class;

    public function definition(): array
    {
        // SCRUM-362: uno de los tres tipos reales (RE/PA/RT), reutilizado por
        // code (firstOrCreate) en vez de crear uno nuevo por comercio —
        // `code` es unique, y en el mundo real solo existen esos tres. Un
        // código aleatorio dejaba cualquier comercio de factory fuera del
        // mapeo de FiscalCodeResolver.
        $establishmentTypeCode = $this->faker->randomElement([
            Constant::ESTABLISHMENT_TYPE_RESTAURANT,
            Constant::ESTABLISHMENT_TYPE_BAKERY,
            Constant::ESTABLISHMENT_TYPE_RETAIL,
        ]);

        $attributes = [
            'owner_user_id' => User::factory(),
            'department_id' => Department::factory(),
            'city_id' => City::factory(),
            'neighborhood_id' => Neighborhood::factory(),
            'establishment_type_id' => fn () => EstablishmentType::firstOrCreate(
                ['code' => $establishmentTypeCode],
                ['name' => $this->faker->word(), 'status' => '1']
            )->id,
            'name' => $this->faker->company(),
            'description' => $this->faker->catchPhrase(),
            'tax_id' => $this->faker->numerify('#########'),
            'tax_id_type' => $this->faker->randomElement(['NIT', 'CC', 'CE']),
            'address' => $this->faker->address(),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'is_active' => $this->faker->boolean(90),
            'is_verified' => $this->faker->randomElement([0, 1]),
            'terms_accepted_version' => $this->faker->boolean(80) ? $this->faker->numberBetween(1, 5) : null,
            'terms_accepted_at' => $this->faker->boolean(80) ? $this->faker->dateTime() : null,
        ];

        // SCRUM-365: solo se fija explícito cuando el tipo lo admite (CR-01
        // prohíbe el campo para RT). Omitir la clave por completo para RT —
        // no ponerla en null ni false — para que un test que reenvíe
        // $commerce->toArray() a un PUT no dispare "no aplica a este tipo".
        // Un factory ->create() tampoco relee la fila insertada, así que un
        // atributo ausente aquí queda ausente de toArray() aunque la columna
        // tenga default de BD.
        if (in_array($establishmentTypeCode, Constant::FRANCHISE_ELIGIBLE_ESTABLISHMENT_TYPE_CODES, true)) {
            $attributes['operates_under_franchise'] = false;
        }

        return $attributes;
    }
}
