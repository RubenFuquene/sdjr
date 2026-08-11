<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Líneas de idioma de productos
    |--------------------------------------------------------------------------
    |
    | SCRUM-227: mensajes de los endpoints de package-items, antes
    | hardcodeados en inglés sin pasar por i18n.
    |
    */

    'package_items' => [
        'store_success' => 'Ítems del pack guardados correctamente.',
        'store_error' => 'Error al guardar los ítems del pack.',
        'get_success' => 'Ítems del pack obtenidos correctamente.',
        'get_not_found' => 'No se encontró el producto con el ID especificado.',
        'get_error' => 'Error al obtener los ítems del pack.',
        'update_success' => 'Ítems del pack actualizados correctamente.',
        'update_error' => 'Error al actualizar los ítems del pack.',
        'delete_not_found' => 'No se encontró el pack del producto.',
        'delete_error' => 'Error al eliminar los ítems del pack.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Composición de pack
    |--------------------------------------------------------------------------
    |
    | SCRUM-361/323/362: validación de package_items en StoreProductRequest y
    | UpdateProductRequest, antes hardcodeada en inglés sin pasar por i18n.
    |
    */

    'package_composition' => [
        'pending_review' => "El producto ':title' no se puede agregar a un pack mientras su clasificación fiscal esté pendiente de revisión.",
        'not_single_type' => "El producto ':title' debe ser de tipo 'individual' para incluirse en un pack.",
        'missing_stock' => "El producto ':title' no tiene stock asignado en la sede ':branch'.",
        'max_packs_exceeded' => 'La cantidad solicitada (:requested) supera el máximo de packs disponibles en la sede \':branch\' según el stock actual (máx: :max).',
        'price_ceiling' => 'El precio del pack debe ser igual a la suma del precio actual de sus componentes (esperado: :expected).',
    ],

    /*
    |--------------------------------------------------------------------------
    | Publicación por sede
    |--------------------------------------------------------------------------
    |
    | UpdateProductBranchPublicationRequest: validación del toggle de publicar
    | por sede, antes hardcodeada en inglés sin pasar por i18n.
    |
    */

    'branch_publication' => [
        'not_assigned' => 'Este producto ya no está asignado a esa sede.',
        'pending_review' => 'No se puede publicar un producto con clasificación fiscal pendiente de revisión.',
        'package_component_pending_review' => "No se puede publicar el pack: el producto ':title' tiene su clasificación fiscal pendiente de revisión.",
        'zero_quantity' => 'No se puede publicar una sede sin inventario/compromiso cargado.',
        'package_capacity_exceeded' => 'No se puede publicar: los componentes en esta sede solo alcanzan para :max pack(s), pero hay :committed comprometidos.',
    ],

];
