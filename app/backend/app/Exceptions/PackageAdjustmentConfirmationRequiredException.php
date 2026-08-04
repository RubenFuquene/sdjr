<?php

declare(strict_types=1);

namespace App\Exceptions;

use Exception;

/**
 * SCRUM-361, Tarea 3.3: el aliado bajó el stock de un componente (o le
 * quitó una sede) y eso deja uno o más packs sobre-comprometidos en esa
 * sede. No se aplica nada hasta que confirme explícitamente — el
 * controlador atrapa esta excepción y responde 409 con el detalle.
 */
class PackageAdjustmentConfirmationRequiredException extends Exception
{
    /**
     * @param  array<int, array{package_id:int, package_title:string, commerce_branch_id:int, current_quantity:int, adjusted_quantity:int}>  $affectedPackages
     */
    public function __construct(private readonly array $affectedPackages)
    {
        parent::__construct('This change affects existing packs and requires confirmation.');
    }

    /**
     * @return array<int, array{package_id:int, package_title:string, commerce_branch_id:int, current_quantity:int, adjusted_quantity:int}>
     */
    public function affectedPackages(): array
    {
        return $this->affectedPackages;
    }
}
