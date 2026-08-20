<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Líneas de idioma de órdenes
    |--------------------------------------------------------------------------
    |
    | SCRUM-376: mensaje deliberadamente neutro de cara al comprador — no
    | nombra el motivo fiscal (D5 del plan). El detalle real queda en log
    | del servidor para soporte.
    |
    */

    'fiscal_classification_unavailable' => "El producto ':title' no está disponible por ahora.",

    /*
    |--------------------------------------------------------------------------
    | Compuertas de catálogo (SCRUM-375)
    |--------------------------------------------------------------------------
    |
    | Mismo criterio de mensaje neutro: al comprador no le importa si el
    | motivo es despublicación, inactividad o vencimiento — para él el
    | producto "no está disponible". El detalle real queda en log.
    |
    */

    'product_unavailable' => "El producto ':title' no está disponible por ahora.",
    'product_unavailable_generic' => 'Uno de los productos solicitados ya no está disponible.',
    'insufficient_stock' => "No hay suficiente disponibilidad de ':title' en este momento.",

];
