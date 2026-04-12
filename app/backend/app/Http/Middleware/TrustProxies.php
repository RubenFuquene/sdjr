<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array<int, string|null>
     */
    protected $proxies;

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_FOR |
        \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_HOST |
        \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_PORT |
        \Symfony\Component\HttpFoundation\Request::HEADER_X_FORWARDED_PROTO;

    public function __construct()
    {
        // Por defecto, confiar en todos los proxies (ajustar en prod si es necesario)
        $this->proxies = '*';
    }
}
