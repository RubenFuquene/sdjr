<?php

namespace App\Enums;

/**
 * Ámbito (módulo) desde el cual se intenta iniciar sesión.
 *
 * El login valida que el rol del usuario pertenezca al ámbito de la pantalla usada
 * (SCRUM-325). El mapa es ESTRICTO: no hay god-mode multi-módulo (p. ej. superadmin
 * NO puede iniciar sesión en provider ni customer).
 */
enum LoginScope: string
{
    case Admin = 'admin';
    case Provider = 'provider';
    case Customer = 'customer';

    /**
     * Roles (nombres Spatie) permitidos para este ámbito.
     *
     * @return list<string>
     */
    public function allowedRoles(): array
    {
        return match ($this) {
            self::Admin => ['superadmin', 'admin', 'support'],
            self::Provider => ['provider', 'branch_leader'],
            self::Customer => ['user'],
        };
    }
}
