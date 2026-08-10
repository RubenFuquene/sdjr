<?php

declare(strict_types=1);

namespace App\Services;

use App\Constants\Constant;
use App\Enums\FiscalCode;
use App\Models\Commerce;

/**
 * SCRUM-362/365: punto único que responde qué códigos fiscales puede usar un
 * comercio. Reutilizado por la validación de producto (Tarea 3), la carga
 * masiva (SCRUM-364) y el futuro motor de FAN (SCRUM-252) — ningún consumidor
 * debe reimplementar esta regla.
 *
 * PendingReview ("otro_verificar") se agrega siempre, sin importar el tipo:
 * no es un tratamiento fiscal disponible sino el placeholder de "todavía sin
 * clasificar", y todo comercio debe poder marcar un producto así (CA-08).
 */
class FiscalCodeResolver
{
    /**
     * Códigos permitidos por tipo de establecimiento, antes de aplicar la
     * exclusión por franquicia. Dato, no cadena de `if`: agregar un tipo de
     * establecimiento nuevo es agregar una fila aquí, no tocar código.
     */
    private const ALLOWED_CODES_BY_ESTABLISHMENT_TYPE = [
        Constant::ESTABLISHMENT_TYPE_RESTAURANT => [
            FiscalCode::ExcludedBasicBasket,
            FiscalCode::Vat19General,
            FiscalCode::Vat5Special,
            FiscalCode::Inc8Prepared,
        ],
        Constant::ESTABLISHMENT_TYPE_BAKERY => [
            FiscalCode::ExcludedBasicBasket,
            FiscalCode::Vat19General,
            FiscalCode::Vat5Special,
            FiscalCode::Inc8Prepared,
        ],
        Constant::ESTABLISHMENT_TYPE_RETAIL => [
            FiscalCode::ExcludedBasicBasket,
            FiscalCode::Vat19General,
            FiscalCode::Vat5Special,
            FiscalCode::Liquor,
        ],
    ];

    /**
     * @return FiscalCode[]
     */
    public function availableFor(Commerce $commerce): array
    {
        $establishmentType = $commerce->establishmentType;

        if (! $establishmentType) {
            return [];
        }

        $codes = self::ALLOWED_CODES_BY_ESTABLISHMENT_TYPE[$establishmentType->code] ?? [];

        if ($commerce->operates_under_franchise) {
            $codes = array_values(array_filter(
                $codes,
                fn (FiscalCode $code) => $code !== FiscalCode::Inc8Prepared
            ));
        }

        $codes[] = FiscalCode::PendingReview;

        return $codes;
    }

    public function isAllowed(Commerce $commerce, FiscalCode $code): bool
    {
        return in_array($code, $this->availableFor($commerce), true);
    }
}
