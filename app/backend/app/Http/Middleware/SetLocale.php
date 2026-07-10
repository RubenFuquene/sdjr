<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resuelve el idioma de la request en cascada y lo fija para el resto del pipeline.
 *
 * Cascada actual (API stateless): cabecera Accept-Language acotada a la whitelist
 * config('app.supported_locales') → idioma por defecto config('app.locale').
 *
 * Punto de extensión: cuando la tabla `users` tenga una columna de preferencia de
 * idioma, anteponer ese nivel a la cascada (usuario autenticado → header → default)
 * sin modificar el resto del pipeline.
 */
class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $supported = config('app.supported_locales', [config('app.locale')]);

        // getPreferredLanguage devuelve el mejor match de la whitelist según los
        // q-values del header; si no hay match, devuelve el primer valor de la lista.
        // Al pasar la whitelist, nunca se resuelve un locale fuera de ella (OWASP A03).
        $locale = $request->getPreferredLanguage($supported) ?? config('app.locale');

        App::setLocale($locale);

        return $next($request);
    }
}
