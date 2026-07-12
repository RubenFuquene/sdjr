<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Nominatim (OpenStreetMap) — geocoding / reverse geocoding
    |--------------------------------------------------------------------------
    |
    | Servicio gratuito de OpenStreetMap. Su política de uso exige identificar
    | la aplicación con un User-Agent válido (y opcionalmente un email de
    | contacto) y respetar un límite de ~1 solicitud/segundo. No requiere
    | registro ni API key: el email es solo un identificador de cortesía,
    | reemplazable en cualquier momento vía variable de entorno.
    | Nunca se llama directamente desde el navegador: siempre vía nuestro
    | proxy backend (GeocodingService), que además cachea y limita la tasa
    | de salida. https://operations.osmfoundation.org/policies/nominatim/
    |
    */
    'nominatim' => [
        'base_url' => env('NOMINATIM_BASE_URL', 'https://nominatim.openstreetmap.org'),
        'user_agent' => env('NOMINATIM_USER_AGENT', 'NAPAAPP/1.0'),
        'contact_email' => env('NOMINATIM_CONTACT_EMAIL'),
    ],

];
