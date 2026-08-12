<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Catálogo de clasificación fiscal de producto (SCRUM-362), validado por el
 * contador. El aliado nunca digita un porcentaje — selecciona una etiqueta y
 * el sistema deriva la tarifa de aquí. Valores en español: son términos del
 * dominio fiscal colombiano citados en la base normativa, no texto de UI.
 */
enum FiscalCode: string
{
    case ExcludedBasicBasket = 'excluido_canasta_basica';
    case Vat19General = 'iva_19_general';
    case Vat5Special = 'iva_5_especial';
    case Liquor = 'licor_ley223';
    case Inc8Prepared = 'inc_8_preparado';
    case PendingReview = 'otro_verificar';

    /**
     * Tarifa de IVA. 0 para PendingReview: un producto en ese estado nunca
     * llega a facturarse (Tarea 4 lo bloquea en publicación y en compra), la
     * tarifa es inerte por diseño, no una clasificación real.
     */
    public function vatRate(): float
    {
        return match ($this) {
            self::ExcludedBasicBasket, self::Inc8Prepared, self::PendingReview => 0.0,
            self::Vat19General => 19.0,
            self::Vat5Special, self::Liquor => 5.0,
        };
    }

    public function appliesInc(): bool
    {
        return $this === self::Inc8Prepared;
    }

    public function incRate(): float
    {
        return $this->appliesInc() ? 8.0 : 0.0;
    }

    /**
     * Etiqueta descriptiva mostrada al aliado — nunca un porcentaje.
     */
    public function label(): string
    {
        return match ($this) {
            self::ExcludedBasicBasket => 'Excluido de IVA (canasta básica)',
            self::Vat19General => 'IVA general',
            self::Vat5Special => 'IVA reducido',
            self::Liquor => 'Licor',
            self::Inc8Prepared => 'Impoconsumo (comida preparada)',
            self::PendingReview => 'No estoy seguro',
        };
    }

    /**
     * Base normativa citada para el desplegable/documentación — SCRUM-362.
     */
    public function legalBasis(): ?string
    {
        return match ($this) {
            self::ExcludedBasicBasket => 'Art. 424 ET',
            self::Vat19General => 'Art. 468 ET',
            self::Vat5Special => 'Art. 468-1 ET',
            self::Liquor => 'Ley 223/1995, Ley 1816/2016',
            self::Inc8Prepared => 'Art. 426 y 512-1 ET',
            self::PendingReview => null,
        };
    }
}
