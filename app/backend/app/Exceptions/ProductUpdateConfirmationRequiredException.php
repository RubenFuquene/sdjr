<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * SCRUM-362/361 (unificación): una edición de producto puede requerir
 * confirmación por dos motivos independientes en el mismo submit —
 * reclasificar a otro_verificar despublica sedes/packs (SCRUM-362, D9), y
 * bajar el stock de un componente puede dejar packs sobre-comprometidos
 * (SCRUM-361, Tarea 3). Antes se modelaban como dos excepciones separadas y
 * solo la que se lanzara primero llegaba al aliado; esta las reemplaza a
 * ambas para que, si los dos motivos aplican en el mismo request, el 409
 * los traiga juntos y el aliado confirme una sola vez.
 */
class ProductUpdateConfirmationRequiredException extends Exception
{
    /**
     * @param  ?array{affected_branches: array<int, array{commerce_branch_id:int, commerce_branch_name:string}>, affected_packages: array<int, array{package_id:int, package_title:string}>}  $fiscalImpact
     * @param  ?array{affected_packages: array<int, array{package_id:int, package_title:string, commerce_branch_id:int, current_quantity:int, adjusted_quantity:int}>}  $stockImpact
     */
    public function __construct(
        private readonly ?array $fiscalImpact,
        private readonly ?array $stockImpact
    ) {
        parent::__construct('This change requires confirmation before it can be applied.');
    }

    /**
     * @return ?array{affected_branches: array<int, array{commerce_branch_id:int, commerce_branch_name:string}>, affected_packages: array<int, array{package_id:int, package_title:string}>}
     */
    public function fiscalImpact(): ?array
    {
        return $this->fiscalImpact;
    }

    /**
     * @return ?array{affected_packages: array<int, array{package_id:int, package_title:string, commerce_branch_id:int, current_quantity:int, adjusted_quantity:int}>}
     */
    public function stockImpact(): ?array
    {
        return $this->stockImpact;
    }
}
