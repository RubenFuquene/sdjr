# Plan: Búsqueda de Sucursales y Productos Cercanos por Geolocalización

## Contexto del Schema Actual

```

commerce_branches

  - id, commerce_id, latitude (decimal 10,7), longitude (decimal 10,7), ...


products

  - id, commerce_id, title, original_price, discounted_price,

    quantity_available, expires_at, status, ...


product_commerce_branch (pivot)

  - product_id → products

  - commerce_branch_id → commerce_branches

```

El cliente enviará su `latitude` y `longitude`. El sistema debe devolver las

sucursales dentro de un radio configurable y los productos disponibles en

cada sucursal cercana, ordenados por distancia.

---

## Paso 1 — Migración: Índice Compuesto en `commerce_branches` (modificar existente)

Editar directamente la migración existente
`database/migrations/2026_01_20_000000_create_commerce_branches_table.php`.
Agregar el índice compuesto `(latitude, longitude)` dentro del mismo `Schema::create`,
no crear una migración nueva para mantener el historial limpio.

```php
// En 2026_01_20_000000_create_commerce_branches_table.php
// Añadir después de las columnas latitude / longitude:
$table->index(['latitude', 'longitude']);
```

> Alternativa de mayor rendimiento para MySQL 5.7+ / 8: usar columna `POINT`
> con `SPATIAL INDEX`. Se puede diferir a una segunda iteración si el volumen
> de datos lo justifica.

---

## Paso 2 — Constante de Radio por Defecto

En `App\Constants\Constant`:

```php

const DEFAULT_SEARCH_RADIUS_KM = 10;// kilómetros

const MAX_SEARCH_RADIUS_KM     = 50;

```

---

## Paso 3 — Scope Haversine en el Modelo `CommerceBranch`

Añadir un query scope reusable que filtra por radio usando la fórmula de

Haversine (sin dependencias externas):

```php

// App\Models\CommerceBranch

publicfunctionscopeNearby(Builder$query,float$lat,float$lng,float$radiusKm): Builder

{

// Fórmula Haversine inline en SQL

return$query

        ->whereNotNull('latitude')

        ->whereNotNull('longitude')

        ->selectRaw(

'*, ( 6371 * acos(

              cos(radians(?)) * cos(radians(latitude)) *

              cos(radians(longitude) - radians(?)) +

              sin(radians(?)) * sin(radians(latitude))

            ) ) AS distance_km',

            [$lat,$lng,$lat]

)

        ->having('distance_km','<=',$radiusKm)

        ->orderBy('distance_km');

}

```

---

## Paso 4 — Service: `NearbySearchService`

Clase nueva en `App\Services\NearbySearchService` con dos métodos públicos:

### 4.1 `nearbyBranches(float $lat, float $lng, float $radius): LengthAwarePaginator`

- Aplica el scope `nearby` sobre `CommerceBranch`.
- Carga relaciones: `commerce`, `hours` (horarios), `photos`.
- Filtra solo sucursales con `status = active`.
- Devuelve paginado.

### 4.2 `nearbyProducts(float $lat, float $lng, float $radius, array $filters = []): LengthAwarePaginator`

- Parte de `Product` con `status = active`, `quantity_available > 0`,

`expires_at IS NULL OR expires_at > now()`.

- Hace `join` con `product_commerce_branch` y luego con `commerce_branches`.
- Aplica la fórmula Haversine sobre `commerce_branches.latitude/longitude`.
- Soporte para filtros opcionales: `category_id`, `max_price`, `commerce_id`.
- Carga relaciones: `photos`, `category`, `branches` (solo las cercanas).
- Devuelve paginado, incluyendo la distancia mínima de la sucursal más cercana

  que vende el producto.

---

## Paso 5 — Form Requests

### `NearbyBranchesRequest`

```

latitude  → required|numeric|between:-90,90

longitude → required|numeric|between:-180,180

radius    → nullable|numeric|min:0.1|max:50  (default: Constant::DEFAULT_SEARCH_RADIUS_KM)

per_page  → nullable|integer|min:1|max:50

```

### `NearbyProductsRequest`

```

latitude    → required|numeric|between:-90,90

longitude   → required|numeric|between:-180,180

radius      → nullable|numeric|min:0.1|max:50

category_id → nullable|exists:product_categories,id

max_price   → nullable|numeric|min:0

per_page    → nullable|integer|min:1|max:50

```

---

## Paso 6 — Resources

### `NearbyBranchResource` (extiende / reutiliza `CommerceBranchResource`)

- Añade el campo `distance_km` (redondeado a 2 decimales).

### `NearbyProductResource` (extiende / reutiliza `ProductResource`)

- Añade `nearest_branch_distance_km`.
- Incluye el branch más cercano (`nearest_branch`).

---

## Paso 7 — Controller: `NearbyController`

```

App\Http\Controllers\Api\V1\NearbyController

```

Dos endpoints GET sin autenticación (acceso público):

| Método | Ruta | Acción |

|--------|------|--------|

| GET | `/api/v1/nearby/branches` | `branches(NearbyBranchesRequest)` |

| GET | `/api/v1/nearby/products` | `products(NearbyProductsRequest)` |

El controller es delgado: valida con FormRequest → delega al Service →

devuelve Resource paginado.

---

## Paso 8 — Rutas

En `routes/api.php`, dentro del grupo `v1`:

```php

Route::prefix('nearby')->name('nearby.')->group(function(){

Route::get('branches', [NearbyController::class,'branches'])->name('branches');

Route::get('products', [NearbyController::class,'products'])->name('products');

});

```

---

## Paso 9 — Tests Feature

Archivo: `tests/Feature/Api/V1/NearbyFeatureTest.php`

Casos a cubrir:

- Retorna solo sucursales dentro del radio.
- Excluye sucursales inactivas.

-`distance_km` está presente y es correcto.

- Retorna solo productos activos con stock y no vencidos.
- Producto sin sucursal cercana no aparece.
- Filtro por `category_id` funciona.
- Filtro por `max_price` funciona.
- Validación falla si `latitude` está fuera de rango.
- Paginación funciona correctamente.

---

## Paso 10 — Documentación Swagger / OpenAPI

El proyecto usa `darkaonline/l5-swagger` + `zircote/swagger-php`.
Toda la documentación se agrega mediante anotaciones PHP (`#[OA\...]`).

### 10.1 Schema `NearbyBranchesRequest` (query params)

```php
#[OA\Parameter(name: 'latitude',  in: 'query', required: true,  schema: new OA\Schema(type: 'number', format: 'float', minimum: -90,  maximum: 90))]
#[OA\Parameter(name: 'longitude', in: 'query', required: true,  schema: new OA\Schema(type: 'number', format: 'float', minimum: -180, maximum: 180))]
#[OA\Parameter(name: 'radius',    in: 'query', required: false, schema: new OA\Schema(type: 'number', format: 'float', minimum: 0.1,  maximum: 50, default: 10))]
#[OA\Parameter(name: 'per_page',  in: 'query', required: false, schema: new OA\Schema(type: 'integer', minimum: 1, maximum: 50, default: 15))]
```

### 10.2 Schema `NearbyProductsRequest` (query params)

```php
#[OA\Parameter(name: 'latitude',    in: 'query', required: true)]
#[OA\Parameter(name: 'longitude',   in: 'query', required: true)]
#[OA\Parameter(name: 'radius',      in: 'query', required: false)]
#[OA\Parameter(name: 'category_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer'))]
#[OA\Parameter(name: 'max_price',   in: 'query', required: false, schema: new OA\Schema(type: 'number', format: 'float'))]
#[OA\Parameter(name: 'per_page',    in: 'query', required: false)]
```

### 10.3 Schema `NearbyBranchResource`

```php
#[OA\Schema(
    schema: 'NearbyBranch',
    allOf: [new OA\Schema(ref: '#/components/schemas/CommerceBranch')],
    properties: [
        new OA\Property(property: 'distance_km', type: 'number', format: 'float', example: 2.34),
    ]
)]
```

### 10.4 Schema `NearbyProductResource`

```php
#[OA\Schema(
    schema: 'NearbyProduct',
    allOf: [new OA\Schema(ref: '#/components/schemas/Product')],
    properties: [
        new OA\Property(property: 'nearest_branch_distance_km', type: 'number', format: 'float', example: 1.20),
        new OA\Property(property: 'nearest_branch', ref: '#/components/schemas/NearbyBranch'),
    ]
)]
```

### 10.5 Endpoints en `NearbyController`

```php
// GET /api/v1/nearby/branches
#[OA\Get(
    path: '/api/v1/nearby/branches',
    summary: 'Sucursales cercanas a una ubicación',
    tags: ['Nearby'],
    parameters: [ /* NearbyBranchesRequest params */ ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Lista paginada de sucursales ordenadas por distancia',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', type: 'array',
                        items: new OA\Items(ref: '#/components/schemas/NearbyBranch')),
                    new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
                ]
            )
        ),
        new OA\Response(response: 422, description: 'Error de validación'),
    ]
)]

// GET /api/v1/nearby/products
#[OA\Get(
    path: '/api/v1/nearby/products',
    summary: 'Productos disponibles en sucursales cercanas',
    tags: ['Nearby'],
    parameters: [ /* NearbyProductsRequest params */ ],
    responses: [
        new OA\Response(
            response: 200,
            description: 'Lista paginada de productos con distancia a la sucursal más cercana',
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'data', type: 'array',
                        items: new OA\Items(ref: '#/components/schemas/NearbyProduct')),
                    new OA\Property(property: 'meta', ref: '#/components/schemas/PaginationMeta'),
                ]
            )
        ),
        new OA\Response(response: 422, description: 'Error de validación'),
    ]
)]
```

---

## Orden de Ejecución Sugerido

1. Modificar migración existente (índice compuesto)
2. Constantes
3. Scope `nearby` en `CommerceBranch`
4. `NearbySearchService`
5. Form Requests
6. Resources
7. Controller + Rutas
8. Documentación Swagger (anotaciones en controller y resources)
9. Tests Feature (pint + phpunit deben pasar)
