<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Commerce;
use App\Models\CommerceFranchiseDeclaration;

/**
 * SCRUM-365: registra el rastro probatorio de cada declaración de
 * franquicia. Deliberadamente separado de CommerceService — es un efecto
 * secundario de la capa HTTP (IP, user-agent), no una responsabilidad del
 * CRUD genérico del comercio.
 */
class CommerceFranchiseDeclarationService
{
    public function record(
        Commerce $commerce,
        bool $operatesUnderFranchise,
        int $declaredByUserId,
        string $ipAddress,
        ?string $userAgent
    ): CommerceFranchiseDeclaration {
        return CommerceFranchiseDeclaration::create([
            'commerce_id' => $commerce->id,
            'operates_under_franchise' => $operatesUnderFranchise,
            'declared_by_user_id' => $declaredByUserId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }
}
