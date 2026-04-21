## Plan: Agregar Campo Quantity a Product Package Items

Se agregará el campo `quantity` (unsignedInteger) a la tabla intermedia `product_package_items` para especificar cuántas unidades de cada producto contiene un paquete. Este cambio implica un breaking change en el API, modificando la estructura de `package_items` de un array de IDs a un array de objetos con `product_id` y `quantity`.

**Steps**

1. Fase de base de datos
   1. Crear migración `add_quantity_to_product_package_items_table` agregando columna `quantity` (unsignedInteger, default: 1).
   2. Ejecutar migración en ambiente local.
   3. Actualizar relaciones en modelo Product agregando `->withPivot('quantity')` en métodos `packageItems()` y `package()`.

2. Fase de validación y contratos
   1. Actualizar validaciones en StoreProductRequest cambiando de `package_items.*` (integer) a estructura de objetos con `package_items.*.product_id` y `package_items.*.quantity` (required, integer, min:1).
   2. Actualizar validaciones en UpdateProductRequest con misma estructura.
   3. Agregar regla `distinct` en `product_id` para prevenir productos duplicados en el mismo paquete.
   4. Actualizar documentación Swagger en ambos Request con esquema de objeto que incluya `product_id` y `quantity`, incluyendo ejemplos.

3. Fase de lógica de negocio
   1. Modificar ProductService::storePackageItems transformando array de items antes de `attach()` para incluir quantity en pivot.
   2. Modificar ProductService::updatePackageItems con misma transformación.
   3. Actualizar ProductResource para incluir `quantity` del pivot en respuestas de `packageItems`.

4. Fase de pruebas
   1. Actualizar tests existentes modificando estructura de datos de prueba para incluir `product_id` y `quantity`:
      - `test_delete_package_items_deletes_all()`
      - `test_get_package_items_returns_items()`
      - `test_get_package_items_returns_empty_when_none()`
   2. Crear nuevos tests de validación:
      - `test_store_package_items_requires_quantity()`
      - `test_store_package_items_rejects_zero_quantity()`
      - `test_store_package_items_rejects_negative_quantity()`
      - `test_update_package_items_updates_quantity()`
      - `test_store_package_items_prevents_duplicate_products()`

5. Fase de verificación
   1. Ejecutar suite completa de tests: `php artisan test`
   2. Regenerar documentación Swagger si aplica
   3. Probar manualmente con Postman/Insomnia todos los endpoints de package items
   4. Validar respuestas incluyan campo quantity correctamente

**Relevant files**
- [app/backend/database/migrations/YYYY_MM_DD_add_quantity_to_product_package_items_table.php](app/backend/database/migrations/) - nueva migración para agregar columna quantity (CREAR).
- [app/backend/app/Models/Product.php](app/backend/app/Models/Product.php) - agregar `withPivot('quantity')` en líneas 131-134 y 141-144.
- [app/backend/app/Services/ProductService.php](app/backend/app/Services/ProductService.php) - transformar datos en `storePackageItems` (~línea 285) y `updatePackageItems` (~línea 309).
- [app/backend/app/Http/Requests/Api/V1/StoreProductRequest.php](app/backend/app/Http/Requests/Api/V1/StoreProductRequest.php) - actualizar validaciones (línea ~91) y documentación Swagger (líneas 42-47).
- [app/backend/app/Http/Requests/Api/V1/UpdateProductRequest.php](app/backend/app/Http/Requests/Api/V1/UpdateProductRequest.php) - actualizar validaciones (línea ~82).
- [app/backend/app/Http/Resources/Api/V1/ProductResource.php](app/backend/app/Http/Resources/Api/V1/ProductResource.php) - incluir `pivot->quantity` en packageItems.
- [app/backend/tests/Feature/Api/V1/ProductFeatureTest.php](app/backend/tests/Feature/Api/V1/ProductFeatureTest.php) - actualizar tests existentes (líneas 178-290) y crear 5 nuevos tests.

**Verification**
1. Confirmar columna `quantity` existe en tabla `product_package_items` tras migración.
2. Probar crear paquete con nuevo formato y validar persistencia en DB con quantity correcto.
3. Probar actualizar paquete y validar que quantity se actualiza correctamente.
4. Probar validaciones: quantity requerido (422), quantity = 0 (422), quantity negativo (422).
5. Probar productos duplicados en mismo request y esperar 422.
6. Validar respuestas GET incluyan campo quantity en cada item del paquete.
7. Ejecutar suite completa de tests y confirmar 100% pasan.

**Decisions**
- Tipo de dato: `unsignedInteger` (solo enteros, no decimales) - confirmado por usuario.
- Valor mínimo: 1 unidad (no permitir 0) - confirmado por usuario.
- Datos existentes: No hay paquetes en BD, no requiere migración de datos - confirmado por usuario.
- Breaking change: Implementar cambio directo sin compatibilidad temporal - confirmado por usuario.
- Valor default: `default(1)` en migración como fallback.
- Validación adicional: `distinct` para prevenir duplicados en mismo request.
- Estructura request: De `package_items: [1, 2, 3]` a `package_items: [{product_id: 1, quantity: 2}, ...]`
- Estructura response: Incluir `quantity` en cada item de packageItems.

**Breaking Changes**
⚠️ Este cambio rompe compatibilidad con clientes existentes del API:
- **Antes**: `POST /api/v1/products/commerce/package-items` con `package_items: [1, 2, 3]`
- **Después**: `POST /api/v1/products/commerce/package-items` con `package_items: [{product_id: 1, quantity: 2}, ...]`
- **Impacto**: Todos los consumidores del API deben actualizar sus requests al crear/actualizar paquetes.
- **Notificación**: Se debe comunicar este breaking change a todos los equipos que consuman estos endpoints.

**Estimated Time**: ~2.5 horas total
- Migración y modelos: 15 min
- Servicios y validadores: 30 min
- Documentación Swagger: 20 min
- Resources: 15 min
- Tests: 45 min
- Testing manual y ajustes: 30 min
