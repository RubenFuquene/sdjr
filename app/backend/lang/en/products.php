<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Product Language Lines
    |--------------------------------------------------------------------------
    |
    | SCRUM-227: package-items endpoint messages, previously hardcoded in
    | English without going through i18n.
    |
    */

    'package_items' => [
        'store_success' => 'Product package items stored successfully.',
        'store_error' => 'Error storing product package items.',
        'get_success' => 'Package items fetched successfully.',
        'get_not_found' => 'Product not found with the specified ID.',
        'get_error' => 'Error fetching package items.',
        'update_success' => 'Product package items updated successfully.',
        'update_error' => 'Error updating product package items.',
        'delete_not_found' => 'Product package not found.',
        'delete_error' => 'Error deleting package items.',
    ],

    /*
    |--------------------------------------------------------------------------
    | Package Composition
    |--------------------------------------------------------------------------
    |
    | SCRUM-361/323/362: package_items validation in StoreProductRequest and
    | UpdateProductRequest, previously hardcoded in English without going
    | through i18n.
    |
    */

    'package_composition' => [
        'pending_review' => "The product ':title' cannot be added to a package while its fiscal classification is pending review.",
        'not_single_type' => "The product ':title' must be of type 'single' to be included in a package.",
        'missing_stock' => "The product ':title' has no stock assigned in branch ':branch'.",
        'max_packs_exceeded' => 'The requested quantity_available (:requested) exceeds the maximum packs available in branch \':branch\' given current stock (max: :max).',
        'price_ceiling' => "The package price must equal the sum of its components' current prices (expected: :expected).",
    ],

    /*
    |--------------------------------------------------------------------------
    | Branch Publication
    |--------------------------------------------------------------------------
    |
    | UpdateProductBranchPublicationRequest: branch-publication toggle
    | validation, previously hardcoded in English without going through i18n.
    |
    */

    'branch_publication' => [
        'not_assigned' => 'This product is not assigned to the given branch.',
        'pending_review' => 'Cannot publish a product with a pending fiscal classification.',
        'package_component_pending_review' => "Cannot publish this package: the product ':title' has a pending fiscal classification.",
        'zero_quantity' => 'Cannot publish a branch with zero available quantity.',
        'package_capacity_exceeded' => 'Cannot publish: components in this branch only support :max pack(s), but :committed are committed.',
    ],

];
