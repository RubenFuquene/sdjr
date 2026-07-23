<?php

declare(strict_types=1);

namespace App\Providers;

use App\Contracts\PaymentGatewayInterface;
use Illuminate\Support\ServiceProvider;
use InvalidArgumentException;

/**
 * Único punto de acoplamiento entre el contrato de pasarela y su
 * implementación concreta (patrón plug-in, ver PaymentGatewayInterface).
 *
 * Primer binding de contenedor por contrato del proyecto: si necesitas
 * otro contrato intercambiable (ej. SMS, facturación), copia este patrón —
 * interfaz en App\Contracts, implementaciones en un namespace propio,
 * selección por config, binding aquí o en un provider dedicado.
 */
class PaymentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PaymentGatewayInterface::class, function ($app) {
            $gateway = (string) config('payments.gateway');
            $map = (array) config('payments.map');

            if (! isset($map[$gateway])) {
                throw new InvalidArgumentException(
                    "Payment gateway [{$gateway}] is not registered in config/payments.php map."
                );
            }

            return $app->make($map[$gateway]);
        });
    }
}
