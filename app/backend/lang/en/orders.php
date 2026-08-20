<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Order language lines
    |--------------------------------------------------------------------------
    |
    | SCRUM-376: message is deliberately neutral toward the buyer — it does
    | not name the fiscal reason (plan decision D5). The real detail stays
    | in the server log for support.
    |
    */

    'fiscal_classification_unavailable' => "The product ':title' is not available right now.",

    /*
    |--------------------------------------------------------------------------
    | Catalog gates (SCRUM-375)
    |--------------------------------------------------------------------------
    |
    | Same neutral-message criterion: the buyer doesn't need to know whether
    | the reason is unpublished, inactive, or expired — for them the product
    | "is not available". The real detail stays in the log.
    |
    */

    'product_unavailable' => "The product ':title' is not available right now.",
    'product_unavailable_generic' => 'One of the requested products is no longer available.',
    'insufficient_stock' => "There isn't enough availability of ':title' right now.",

];
