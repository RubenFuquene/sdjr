<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * SCRUM-362 (D9): reclasificar un producto a `otro_verificar` despublica en
 * cascada las sedes donde está publicado y los packs que lo contienen. Es
 * una acción legal pero con efecto destructivo no obvio — el aliado cree
 * estar corrigiendo una clasificación y termina bajando ventas. No se aplica
 * nada hasta que confirme explícitamente; el controlador atrapa esta
 * excepción y responde 409 con el detalle del impacto.
 */
class FiscalReclassificationConfirmationRequiredException extends Exception
{
    /**
     * @param  array<int, array{commerce_branch_id:int, commerce_branch_name:string}>  $affectedBranches
     * @param  array<int, array{package_id:int, package_title:string}>  $affectedPackages
     */
    public function __construct(
        private readonly array $affectedBranches,
        private readonly array $affectedPackages
    ) {
        parent::__construct('This change will unpublish the product and requires confirmation.');
    }

    /**
     * @return array<int, array{commerce_branch_id:int, commerce_branch_name:string}>
     */
    public function affectedBranches(): array
    {
        return $this->affectedBranches;
    }

    /**
     * @return array<int, array{package_id:int, package_title:string}>
     */
    public function affectedPackages(): array
    {
        return $this->affectedPackages;
    }
}
