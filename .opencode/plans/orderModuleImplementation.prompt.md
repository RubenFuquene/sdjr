# 📋 Plan de Implementación - Módulo de Órdenes

**Fecha de creación:** 18 de Marzo, 2026
**Estado:** 🔴 Pendiente de aprobación
**Versión:** 1.0

---

## 📑 Tabla de Contenidos

- [1. Resumen Ejecutivo](#1-resumen-ejecutivo)
- [2. Estructura de Datos](#2-estructura-de-datos)
- [3. Plan de Trabajo Detallado](#3-plan-de-trabajo-detallado)
- [4. Resumen de Archivos](#4-resumen-de-archivos)
- [5. Orden de Implementación](#5-orden-de-implementación)
- [6. Comandos de Ejecución](#6-comandos-de-ejecución)
- [7. Criterios de Aceptación](#7-criterios-de-aceptación)
- [8. Extras Opcionales](#8-extras-opcionales)

---

## 1. Resumen Ejecutivo

Este documento describe el plan completo para implementar el módulo de gestión de órdenes en el sistema SDJR. El módulo permitirá a los usuarios crear órdenes de compra de productos de diferentes sucursales de comercios.

### Objetivos

- ✅ Crear endpoints completos para gestión de órdenes (CRUD)
- ✅ Implementar relación many-to-many entre órdenes y productos
- ✅ Gestionar estados de órdenes (pending, confirmed, preparing, ready, delivered, cancelled)
- ✅ Mantener consistencia con las convenciones del proyecto
- ✅ Documentar completamente con Swagger
- ✅ Implementar tests exhaustivos

### Decisiones de Diseño

Basado en las respuestas del usuario:

| Aspecto                      | Decisión                                                              |
| ---------------------------- | ---------------------------------------------------------------------- |
| **Tabla pivote**       | `order_items` con campos adicionales: `quantity`, `unit_price`   |
| **Estado de órdenes** | Enum en tabla `orders` (no tabla catálogo separada)                 |
| **Relación commerce** | Solo `commerce_branch_id` (el comercio se obtiene desde la sucursal) |
| **Campos adicionales** | Solo campos básicos (sin delivery_address, payment_method por ahora)  |

---

## 2. Estructura de Datos

### 2.1 Diagrama Entidad-Relación

```
┌─────────────┐         ┌──────────────┐         ┌─────────────────┐
│    users    │         │    orders    │         │  order_items    │
├─────────────┤         ├──────────────┤         ├─────────────────┤
│ id          │◄───┐    │ id           │◄───┐    │ id              │
│ name        │    └────│ user_id      │    └────│ order_id        │
│ email       │         │ commerce_br..│         │ product_id      │
│ ...         │    ┌────│              │    ┌────│                 │
└─────────────┘    │    │ total_price  │    │    │ quantity        │
                   │    │ status       │    │    │ unit_price      │
┌─────────────────┐│    │ created_at   │    │    │ created_at      │
│ commerce_bran.. ││    │ updated_at   │    │    │ updated_at      │
├─────────────────┤│    │ deleted_at   │    │    └─────────────────┘
│ id              │┘    └──────────────┘    │
│ commerce_id     │                         │
│ name            │                         │
│ ...             │         ┌───────────┐   │
└─────────────────┘         │ products  │   │
                            ├───────────┤   │
                            │ id        │◄──┘
                            │ title     │
                            │ price     │
                            │ ...       │
                            └───────────┘
```

### 2.2 Tabla `orders`

```sql
CREATE TABLE orders (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    user_id BIGINT UNSIGNED NOT NULL,
    commerce_branch_id BIGINT UNSIGNED NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'confirmed', 'preparing', 'ready', 'delivered', 'cancelled') DEFAULT 'pending',
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    deleted_at TIMESTAMP NULL,
  
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (commerce_branch_id) REFERENCES commerce_branches(id) ON DELETE CASCADE,
  
    INDEX idx_user_id (user_id),
    INDEX idx_commerce_branch_id (commerce_branch_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
);
```

**Campos:**

| Campo                  | Tipo          | Descripción                        | Requerido |
| ---------------------- | ------------- | ----------------------------------- | --------- |
| `id`                 | bigint        | ID único de la orden               | Sí       |
| `user_id`            | bigint        | Usuario que realiza la orden        | Sí       |
| `commerce_branch_id` | bigint        | Sucursal donde se realiza la orden  | Sí       |
| `total_price`        | decimal(10,2) | Precio total de la orden            | Sí       |
| `status`             | enum          | Estado actual de la orden           | Sí       |
| `created_at`         | timestamp     | Fecha de creación                  | Auto      |
| `updated_at`         | timestamp     | Fecha de última actualización     | Auto      |
| `deleted_at`         | timestamp     | Fecha de eliminación (soft delete) | No        |

**Estados posibles:**

- `pending` - Orden creada, pendiente de confirmación
- `confirmed` - Orden confirmada por el comercio
- `preparing` - Orden en preparación
- `ready` - Orden lista para entrega/pickup
- `delivered` - Orden entregada al cliente
- `cancelled` - Orden cancelada

### 2.3 Tabla `order_items`

```sql
CREATE TABLE order_items (
    id BIGINT UNSIGNED PRIMARY KEY AUTO_INCREMENT,
    order_id BIGINT UNSIGNED NOT NULL,
    product_id BIGINT UNSIGNED NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
  
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
  
    UNIQUE KEY unique_order_product (order_id, product_id),
    INDEX idx_order_id (order_id),
    INDEX idx_product_id (product_id)
);
```

**Campos:**

| Campo          | Tipo          | Descripción                            | Requerido |
| -------------- | ------------- | --------------------------------------- | --------- |
| `id`         | bigint        | ID único del item                      | Sí       |
| `order_id`   | bigint        | Referencia a la orden                   | Sí       |
| `product_id` | bigint        | Referencia al producto                  | Sí       |
| `quantity`   | integer       | Cantidad de productos                   | Sí       |
| `unit_price` | decimal(10,2) | Precio unitario al momento de la compra | Sí       |
| `created_at` | timestamp     | Fecha de creación                      | Auto      |
| `updated_at` | timestamp     | Fecha de última actualización         | Auto      |

**Nota:** Se almacena `unit_price` para mantener un registro histórico del precio al momento de la compra, ya que los precios de productos pueden cambiar con el tiempo.

---

## 3. Plan de Trabajo Detallado

### FASE 1: Base de Datos

#### 📝 Tarea 1.1: Actualizar Constantes

**Archivo:** `app/backend/app/Constants/Constant.php`

**Agregar:**

```php
// Order Status
public const ORDER_STATUS_PENDING = 'pending';
public const ORDER_STATUS_CONFIRMED = 'confirmed';
public const ORDER_STATUS_PREPARING = 'preparing';
public const ORDER_STATUS_READY = 'ready';
public const ORDER_STATUS_DELIVERED = 'delivered';
public const ORDER_STATUS_CANCELLED = 'cancelled';

// Order validations
public const MIN_ORDER_QUANTITY = 1;
public const MAX_ORDER_ITEMS = 50;
```

**Tiempo estimado:** 5 minutos

---

#### 📝 Tarea 1.2: Migración - Tabla Orders

**Archivo nuevo:** `app/backend/database/migrations/YYYY_MM_DD_HHMMSS_create_orders_table.php`

**Contenido:**

```php
<?php

declare(strict_types=1);

use App\Constants\Constant;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
          
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
          
            $table->foreignId('commerce_branch_id')
                ->constrained('commerce_branches')
                ->cascadeOnDelete();
          
            $table->decimal('total_price', 10, 2);
          
            $table->enum('status', [
                Constant::ORDER_STATUS_PENDING,
                Constant::ORDER_STATUS_CONFIRMED,
                Constant::ORDER_STATUS_PREPARING,
                Constant::ORDER_STATUS_READY,
                Constant::ORDER_STATUS_DELIVERED,
                Constant::ORDER_STATUS_CANCELLED,
            ])->default(Constant::ORDER_STATUS_PENDING);
          
            $table->timestamps();
            $table->softDeletes();
          
            // Indices para mejorar performance
            $table->index('user_id');
            $table->index('commerce_branch_id');
            $table->index('status');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
```

**Tiempo estimado:** 10 minutos

---

#### 📝 Tarea 1.3: Migración - Tabla Order Items

**Archivo nuevo:** `app/backend/database/migrations/YYYY_MM_DD_HHMMSS_create_order_items_table.php`

**Contenido:**

```php
<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_items', function (Blueprint $table) {
            $table->id();
          
            $table->foreignId('order_id')
                ->constrained('orders')
                ->cascadeOnDelete();
          
            $table->foreignId('product_id')
                ->constrained('products')
                ->cascadeOnDelete();
          
            $table->integer('quantity');
            $table->decimal('unit_price', 10, 2);
          
            $table->timestamps();
          
            // Prevenir duplicados: un producto solo puede aparecer una vez por orden
            $table->unique(['order_id', 'product_id']);
          
            // Indices
            $table->index('order_id');
            $table->index('product_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('order_items');
    }
};
```

**Tiempo estimado:** 10 minutos

---

### FASE 2: Modelos

#### 📝 Tarea 2.1: Modelo Order

**Archivo nuevo:** `app/backend/app/Models/Order.php`

**Estructura:**

```php
<?php

declare(strict_types=1);

namespace App\Models;

use App\Constants\Constant;
use App\Models\Traits\SanitizesTextAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @OA\Schema(
 *   schema="Order",
 *   type="object",
 *   required={"id", "user_id", "commerce_branch_id", "total_price", "status"},
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="user_id", type="integer"),
 *   @OA\Property(property="commerce_branch_id", type="integer"),
 *   @OA\Property(property="total_price", type="number", format="float"),
 *   @OA\Property(property="status", type="string", enum={"pending","confirmed","preparing","ready","delivered","cancelled"}),
 *   @OA\Property(property="created_at", type="string", format="date-time"),
 *   @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Order extends Model
{
    use HasFactory, SoftDeletes, SanitizesTextAttributes;

    protected $fillable = [
        'user_id',
        'commerce_branch_id',
        'total_price',
        'status',
    ];

    protected $casts = [
        'id' => 'integer',
        'user_id' => 'integer',
        'commerce_branch_id' => 'integer',
        'total_price' => 'float',
    ];

    // Relaciones
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commerceBranch(): BelongsTo
    {
        return $this->belongsTo(CommerceBranch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    // Accessor para obtener el comercio desde la sucursal
    public function getCommerceAttribute()
    {
        return $this->commerceBranch?->commerce;
    }

    // Scopes
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByCommerceBranch($query, int $branchId)
    {
        return $query->where('commerce_branch_id', $branchId);
    }
}
```

**Tiempo estimado:** 15 minutos

---

#### 📝 Tarea 2.2: Modelo OrderItem

**Archivo nuevo:** `app/backend/app/Models/OrderItem.php`

**Estructura:**

```php
<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @OA\Schema(
 *   schema="OrderItem",
 *   type="object",
 *   required={"id", "order_id", "product_id", "quantity", "unit_price"},
 *   @OA\Property(property="id", type="integer"),
 *   @OA\Property(property="order_id", type="integer"),
 *   @OA\Property(property="product_id", type="integer"),
 *   @OA\Property(property="quantity", type="integer"),
 *   @OA\Property(property="unit_price", type="number", format="float"),
 *   @OA\Property(property="subtotal", type="number", format="float")
 * )
 */
class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'unit_price',
    ];

    protected $casts = [
        'id' => 'integer',
        'order_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'unit_price' => 'float',
    ];

    // Relaciones
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    // Accessor para calcular subtotal
    public function getSubtotalAttribute(): float
    {
        return $this->quantity * $this->unit_price;
    }
}
```

**Tiempo estimado:** 10 minutos

---

### FASE 3: Factories & Seeders

#### 📝 Tarea 3.1: Factory - OrderFactory

**Archivo nuevo:** `app/backend/database/factories/OrderFactory.php`

**Estructura:**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Constants\Constant;
use App\Models\CommerceBranch;
use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'commerce_branch_id' => CommerceBranch::factory(),
            'total_price' => $this->faker->randomFloat(2, 10, 1000),
            'status' => $this->faker->randomElement([
                Constant::ORDER_STATUS_PENDING,
                Constant::ORDER_STATUS_CONFIRMED,
                Constant::ORDER_STATUS_PREPARING,
                Constant::ORDER_STATUS_READY,
                Constant::ORDER_STATUS_DELIVERED,
            ]),
        ];
    }

    // Estados específicos
    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Constant::ORDER_STATUS_PENDING,
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Constant::ORDER_STATUS_CONFIRMED,
        ]);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => Constant::ORDER_STATUS_DELIVERED,
        ]);
    }
}
```

**Tiempo estimado:** 10 minutos

---

#### 📝 Tarea 3.2: Factory - OrderItemFactory

**Archivo nuevo:** `app/backend/database/factories/OrderItemFactory.php`

**Estructura:**

```php
<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $product = Product::factory()->create();
      
        return [
            'order_id' => Order::factory(),
            'product_id' => $product->id,
            'quantity' => $this->faker->numberBetween(1, 10),
            'unit_price' => $product->original_price ?? $this->faker->randomFloat(2, 5, 500),
        ];
    }
}
```

**Tiempo estimado:** 10 minutos

---

#### 📝 Tarea 3.3: Seeder - OrderSeeder

**Archivo nuevo:** `app/backend/database/seeders/OrderSeeder.php`

**Estructura:**

```php
<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\CommerceBranch;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        if (env('APP_ENV') == 'prd') {
            // No seedear en producción
            return;
        }
      
        if (env('DEMO_SEEDING') !== 'true') {
            return;
        }
    }
}
```

**Tiempo estimado:** 15 minutos

---

#### 📝 Tarea 3.4: Actualizar DatabaseSeeder

**Archivo:** `app/backend/database/seeders/DatabaseSeeder.php`

**Modificación:**
Agregar al final de la lista de seeders (después de ProductSeeder):

```php
$this->call([
    // ... seeders existentes
    ProductSeeder::class,
    OrderSeeder::class, // ← AGREGAR ESTA LÍNEA
]);
```

**Tiempo estimado:** 2 minutos

---

### FASE 4: Requests (Validaciones)

#### 📝 Tarea 4.1: StoreOrderRequest

**Archivo nuevo:** `app/backend/app/Http/Requests/Api/V1/StoreOrderRequest.php`

Ver contenido completo en sección detallada del documento.

**Tiempo estimado:** 15 minutos

---

#### 📝 Tarea 4.2: UpdateOrderRequest

**Archivo nuevo:** `app/backend/app/Http/Requests/Api/V1/UpdateOrderRequest.php`

**Tiempo estimado:** 10 minutos

---

#### 📝 Tarea 4.3: IndexOrderRequest

**Archivo nuevo:** `app/backend/app/Http/Requests/Api/V1/IndexOrderRequest.php`

**Tiempo estimado:** 10 minutos

---

#### 📝 Tarea 4.4: ShowOrderRequest

**Archivo nuevo:** `app/backend/app/Http/Requests/Api/V1/ShowOrderRequest.php`

**Tiempo estimado:** 5 minutos

---

#### 📝 Tarea 4.5: DeleteOrderRequest

**Archivo nuevo:** `app/backend/app/Http/Requests/Api/V1/DeleteOrderRequest.php`

**Tiempo estimado:** 5 minutos

---

### FASE 5: Resources (Transformación de Datos)

#### 📝 Tarea 5.1: OrderResource

**Archivo nuevo:** `app/backend/app/Http/Resources/Api/V1/OrderResource.php`

**Tiempo estimado:** 10 minutos

---

#### 📝 Tarea 5.2: OrderItemResource

**Archivo nuevo:** `app/backend/app/Http/Resources/Api/V1/OrderItemResource.php`

**Tiempo estimado:** 10 minutos

---

### FASE 6: Service (Lógica de Negocio)

#### 📝 Tarea 6.1: OrderService

**Archivo nuevo:** `app/backend/app/Services/OrderService.php`

**Métodos principales:**

- `index()` - Listar con filtros
- `store()` - Crear orden (con transacción DB)
- `show()` - Obtener orden
- `update()` - Actualizar status
- `destroy()` - Soft delete
- `getByUser()` - Órdenes de usuario
- `getByCommerceBranch()` - Órdenes de sucursal
- `validateStatusTransition()` - Validar cambios de estado

**Tiempo estimado:** 30 minutos

---

### FASE 7: Controller

#### 📝 Tarea 7.1: OrderController

**Archivo nuevo:** `app/backend/app/Http/Controllers/Api/V1/OrderController.php`

**Métodos:**

- `index()` - GET /api/v1/orders
- `store()` - POST /api/v1/orders
- `show()` - GET /api/v1/orders/{id}
- `update()` - PATCH /api/v1/orders/{id}
- `destroy()` - DELETE /api/v1/orders/{id}
- `myOrders()` - GET /api/v1/my-orders
- `commerceBranchOrders()` - GET /api/v1/commerce-branches/{branchId}/orders

**Tiempo estimado:** 30 minutos

---

### FASE 8: Rutas

#### 📝 Tarea 8.1: Actualizar rutas API

**Archivo:** `app/backend/routes/api.php`

**Agregar:**

```php
Route::middleware('auth:sanctum')->group(function () {
    // Orders - CRUD completo
    Route::apiResource('orders', OrderController::class);
  
    // Orders - Rutas personalizadas
    Route::get('my-orders', [OrderController::class, 'myOrders']);
    Route::get('commerce-branches/{branchId}/orders', [OrderController::class, 'commerceBranchOrders']);
});
```

**Tiempo estimado:** 5 minutos

---

### FASE 9: Permisos

#### 📝 Tarea 9.1: Agregar permisos en seeder

**Archivo:** Seeder de permisos existente (RolePermissionSeeder)

**Permisos a crear:**

- customer.orders.create
- customer.orders.show
- customer.orders.index
- provider.orders.index
- provider.orders.show
- provider.orders.update

**Tiempo estimado:** 10 minutos

---

### FASE 10: Tests

#### 📝 Tarea 10.1: Feature Test - OrderFeatureTest

**Archivo nuevo:** `app/backend/tests/Feature/Api/V1/OrderFeatureTest.php`

**Test cases:**

- test_guest_cannot_create_order
- test_customer_can_create_order
- test_create_order_calculates_total_correctly
- test_customer_can_view_own_orders
- test_customer_cannot_view_other_orders
- test_provider_can_view_branch_orders
- test_provider_can_update_order_status
- test_customer_cannot_update_order_status
- test_validates_required_fields
- test_cannot_order_from_inactive_products
- test_my_orders_returns_only_authenticated_user_orders
- test_can_filter_orders_by_status
- test_customer_can_delete_pending_order
- test_customer_cannot_delete_confirmed_order

**Tiempo estimado:** 40 minutos

---

### FASE 11: Documentación Swagger

#### 📝 Tarea 11.1: Generar documentación Swagger

**Comando:**

```bash
php artisan l5-swagger:generate
```

**Tiempo estimado:** 5 minutos

---

## 4. Resumen de Archivos

### 📁 Archivos Nuevos (20 archivos)

```
app/backend/
├── database/
│   ├── migrations/
│   │   ├── YYYY_MM_DD_HHMMSS_create_orders_table.php
│   │   └── YYYY_MM_DD_HHMMSS_create_order_items_table.php
│   ├── factories/
│   │   ├── OrderFactory.php
│   │   └── OrderItemFactory.php
│   └── seeders/
│       └── OrderSeeder.php
├── app/
│   ├── Models/
│   │   ├── Order.php
│   │   └── OrderItem.php
│   ├── Http/
│   │   ├── Requests/Api/V1/
│   │   │   ├── StoreOrderRequest.php
│   │   │   ├── UpdateOrderRequest.php
│   │   │   ├── IndexOrderRequest.php
│   │   │   ├── ShowOrderRequest.php
│   │   │   └── DeleteOrderRequest.php
│   │   ├── Resources/Api/V1/
│   │   │   ├── OrderResource.php
│   │   │   └── OrderItemResource.php
│   │   └── Controllers/Api/V1/
│   │       └── OrderController.php
│   └── Services/
│       └── OrderService.php
└── tests/
    ├── Feature/Api/V1/
    │   └── OrderFeatureTest.php
    └── Unit/
        └── OrderServiceTest.php
```

### 📝 Archivos Modificados (4 archivos)

```
app/backend/
├── app/Constants/Constant.php
├── database/seeders/DatabaseSeeder.php
├── routes/api.php
└── database/seeders/RolePermissionSeeder.php (o similar)
```

---

## 5. Orden de Implementación

### Secuencia recomendada:

1. ✅ **Constantes** → `Constant.php`
2. ✅ **Migraciones** → `create_orders_table.php`, `create_order_items_table.php`
3. ✅ **Modelos** → `Order.php`, `OrderItem.php`
4. ✅ **Factories** → `OrderFactory.php`, `OrderItemFactory.php`
5. ✅ **Seeders** → `OrderSeeder.php`, actualizar `DatabaseSeeder.php`
6. ✅ **Requests** → 5 archivos de validación
7. ✅ **Resources** → `OrderResource.php`, `OrderItemResource.php`
8. ✅ **Service** → `OrderService.php`
9. ✅ **Controller** → `OrderController.php`
10. ✅ **Rutas** → `api.php`
11. ✅ **Permisos** → Actualizar seeder de permisos
12. ✅ **Tests** → `OrderFeatureTest.php`
13. ✅ **Documentación** → Generar Swagger

---

## 6. Comandos de Ejecución

### Después de implementar todo:

```bash
# 1. Ir al directorio backend
cd app/backend

# 2. Correr migraciones
docker exec infra-backend-1 php artisan migrate

# 3. (Opcional) Refresh completo si es entorno local
docker exec infra-backend-1 php artisan migrate:fresh

# 4. Ejecutar seeders
docker exec infra-backend-1 php artisan db:seed

# 5. Generar documentación Swagger
docker exec infra-backend-1 php artisan l5-swagger:generate

# 6. Ejecutar tests
docker exec infra-backend-1 php artisan test --filter OrderFeatureTest

# 7. Ejecutar todos los tests
docker exec infra-backend-1 php artisan test

# 8. Linting con Pint
docker exec infra-backend-1 ./vendor/bin/pint

# 9. Limpiar cachés
docker exec infra-backend-1 php artisan config:clear
docker exec infra-backend-1 php artisan cache:clear
docker exec infra-backend-1 php artisan route:clear
```

---

## 7. Criterios de Aceptación

### ✅ Funcionales

- [ ] Usuario autenticado puede crear una orden con múltiples productos
- [ ] El sistema calcula correctamente el total de la orden
- [ ] Se valida disponibilidad de productos antes de crear orden
- [ ] Se almacena el precio unitario histórico de cada producto
- [ ] Provider puede ver órdenes de sus sucursales
- [ ] Provider puede actualizar el estado de órdenes
- [ ] Cliente puede ver solo sus propias órdenes
- [ ] Cliente puede cancelar órdenes en estado "pending"
- [ ] Las transiciones de estado siguen reglas de negocio válidas
- [ ] Filtros funcionan correctamente (status, fecha, sucursal)

### ✅ Técnicos

- [ ] Todas las migraciones corren sin errores
- [ ] Todos los tests pasan (`php artisan test`)
- [ ] Documentación Swagger se genera correctamente
- [ ] Código sigue convenciones del proyecto (strict types, traits, etc.)
- [ ] Sin errores de linting (`./vendor/bin/pint`)
- [ ] Relaciones de base de datos correctamente configuradas
- [ ] Foreign keys con cascade delete
- [ ] Índices en columnas de búsqueda frecuente
- [ ] Soft deletes implementado
- [ ] Logs de errores con contexto adecuado

### ✅ Seguridad

- [ ] Autorización con Spatie Permission
- [ ] Usuarios solo ven sus propias órdenes
- [ ] Providers solo ven órdenes de sus sucursales
- [ ] Validaciones exhaustivas en Requests
- [ ] Transacciones DB para operaciones críticas

---

## 8. Extras Opcionales

### Posibles mejoras futuras (NO incluidas en este plan):

#### 8.1 Historial de Estados

Crear tabla `order_history` para auditoría de cambios de estado.

#### 8.2 Notificaciones

Implementar notificaciones cuando cambie el estado (email, push).

#### 8.3 Información de Pago y Entrega

Agregar campos: payment_method, delivery_address, estimated_delivery_time, etc.

#### 8.4 Integración con Inventario

Reducir automáticamente `quantity_available` al crear orden.

#### 8.5 Sistema de Reviews

Permitir al cliente calificar la orden después de entregarla.

#### 8.6 Reportes y Analytics

Órdenes por sucursal, productos más vendidos, ingresos por período.

#### 8.7 Cupones y Descuentos

Sistema de cupones aplicables a órdenes.

#### 8.8 Orden Recurrente

Permitir repetir órdenes anteriores con un clic.

---

## 9. Notas Finales

### Convenciones a Seguir:

- ✅ `declare(strict_types=1);` en todos los archivos PHP
- ✅ Type hints en todos los métodos
- ✅ Documentación Swagger completa
- ✅ Try-catch en controladores
- ✅ Logs con contexto en servicios
- ✅ Usar constantes en lugar de strings hardcodeados
- ✅ Traits reutilizables (`ApiResponseTrait`, `SanitizesTextAttributes`)
- ✅ Soft deletes en modelos principales

### Tiempo Estimado Total:

**~4-5 horas** de implementación completa (incluyendo tests)

### Próximos Pasos Después de Aprobación:

1. Revisar y aprobar este plan
2. Hacer ajustes si es necesario
3. Comenzar implementación fase por fase
4. Ejecutar tests después de cada fase
5. Revisión final y deployment

---

**Documento elaborado por:** OpenCode AI
**Última actualización:** 18 de Marzo, 2026
**Estado:** 🔴 Pendiente de aprobación
