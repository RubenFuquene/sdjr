<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Líneas de idioma de límite de solicitudes
    |--------------------------------------------------------------------------
    |
    | Mensaje genérico para cualquier endpoint con límite de solicitudes
    | (login, recuperación de contraseña, registro, órdenes, búsqueda
    | cercana...). Separado de la clave 'throttle' de auth.php, que es el
    | texto propio de Laravel para el bloqueo por intentos de login y
    | resultaría engañoso en endpoints que no son de login (SCRUM-354).
    |
    */

    'too_many_requests' => 'Demasiadas solicitudes. Por favor intenta de nuevo más tarde.',

];
