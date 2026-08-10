<?php

declare(strict_types=1);

namespace App\Traits;

use App\Models\EstablishmentType;
use Illuminate\Contracts\Validation\Validator;

/**
 * SCRUM-365: la declaración de franquicia solo aplica a establecimientos que
 * prestan servicio de expendio de comidas (Art. 426 ET) — obligatoria para
 * ellos, prohibida para el resto (CR-01). Punto único reutilizado por los
 * tres form requests que escriben datos del comercio, para que la regla no
 * se reimplemente tres veces con el riesgo de divergir.
 */
trait ValidatesFranchiseDeclaration
{
    protected function addFranchiseDeclarationErrors(Validator $validator, ?int $establishmentTypeId, string $fieldPath): void
    {
        if (! $establishmentTypeId) {
            return;
        }

        $establishmentType = EstablishmentType::find($establishmentTypeId);

        if (! $establishmentType) {
            return;
        }

        $isProvided = $this->has($fieldPath);

        if ($establishmentType->isFranchiseEligible()) {
            if (! $isProvided) {
                $validator->errors()->add($fieldPath, 'The operates_under_franchise field is required for this establishment type.');
            }

            return;
        }

        if ($isProvided) {
            $validator->errors()->add($fieldPath, 'The operates_under_franchise field does not apply to this establishment type.');
        }
    }
}
