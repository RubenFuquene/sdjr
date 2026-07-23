<?php

use App\Payments\Gateways\FakePaymentGateway;

return [

    /*
    |--------------------------------------------------------------------------
    | Pasarela de pagos activa
    |--------------------------------------------------------------------------
    |
    | Clave del map de abajo. Cambiar la pasarela = cambiar esta variable de
    | entorno (+ tener la clase registrada en el map). Ningún archivo de
    | dominio, órdenes, HTTP ni frontend cambia: PaymentServiceProvider
    | bindea PaymentGatewayInterface a la clase resuelta desde aquí.
    |
    | 'fake' es la pasarela simulada (sin proveedor real contratado):
    | aprueba todo, rechaza con el flag de prueba simulate=reject.
    |
    */

    'gateway' => env('PAYMENTS_GATEWAY', 'fake'),

    'map' => [
        'fake' => FakePaymentGateway::class,
        // 'wompi' => WompiPaymentGateway::class,   // futura pasarela real
    ],

    /*
    |--------------------------------------------------------------------------
    | Moneda por defecto
    |--------------------------------------------------------------------------
    */

    'currency' => env('PAYMENTS_CURRENCY', 'COP'),

];
