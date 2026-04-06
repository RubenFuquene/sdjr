## Plan: Aceptación de Términos en Commerce

Vamos a incorporar los campos terms_accepted_at y terms_accepted_version en commerces y exponer PATCH /api/v1/commerces/{id}/accept-terms siguiendo el patrón actual de status/verification, con permiso nuevo provider.commerces.accept-terms, validación dedicada y pruebas feature.

**Steps**
1. Fase de contrato y reglas
1. Confirmar contrato del endpoint: body con terms_accepted_version obligatorio (entero >= 0), respuesta 200 en éxito, y manejo de 403/404/422/500 con el mismo estilo de ApiResponseTrait.
1. Definir semántica de aceptación: al aceptar se persisten terms_accepted_version y terms_accepted_at = now().

2. Fase de persistencia y modelo
1. Usar la una migration actual commerces para añadir columnas terms_accepted_at (timestamp nullable) y terms_accepted_version (integer nullable).
1. Extender el modelo Commerce con fillable/casts para los nuevos campos.
1. Exponer ambos campos en el resource para que salgan en respuestas API.

3. Fase de API (request + service + controller + ruta)
1. Crear Request dedicado para autorización y validación:
   permiso provider.commerces.accept-terms y regla de terms_accepted_version.
1. Agregar método de negocio en CommerceService para actualizar versión y timestamp en transacción.
1. Agregar método acceptTerms en CommerceController siguiendo el patrón de patchStatus/patchVerification.
1. Registrar la ruta PATCH /api/v1/commerces/{id}/accept-terms dentro del grupo commerces.

4. Fase de permisos y pruebas
1. Agregar provider.commerces.accept-terms en el seeder de permisos y asignarlo a los roles que correspondan.
1. Añadir pruebas feature para casos:
   éxito, sin permiso (403), validación inválida (422), comercio inexistente (404).
1. Ajustar factory para facilitar escenarios de pruebas con términos aceptados/no aceptados.

5. Fase de verificación
1. Ejecutar migration en entorno local.
1. Ejecutar tests focalizados de commerce patch endpoints (nuevo + regresión de status y verification).
1. Ejecutar Pint y luego una corrida de tests más amplia si se desea validar regresión global.

**Relevant files**
- [app/backend/database/migrations/2025_12_15_200000_create_commerces_table.php](app/backend/database/migrations/2025_12_15_200000_create_commerces_table.php) - referencia del esquema actual de commerces.
- [app/backend/app/Models/Commerce.php](app/backend/app/Models/Commerce.php) - fillable, casts y schema del modelo.
- [app/backend/app/Http/Resources/Api/V1/CommerceResource.php](app/backend/app/Http/Resources/Api/V1/CommerceResource.php) - salida JSON y documentación de campos.
- [app/backend/app/Services/CommerceService.php](app/backend/app/Services/CommerceService.php) - lógica de negocio para actualización de términos.
- [app/backend/app/Http/Controllers/Api/V1/CommerceController.php](app/backend/app/Http/Controllers/Api/V1/CommerceController.php) - nuevo método acceptTerms con manejo de errores y OpenAPI.
- [app/backend/routes/api.php](app/backend/routes/api.php) - alta de ruta PATCH en el grupo de commerces.
- [app/backend/database/seeders/RolePermissionSeeder.php](app/backend/database/seeders/RolePermissionSeeder.php) - nuevo permiso y asignación.
- [app/backend/database/factories/CommerceFactory.php](app/backend/database/factories/CommerceFactory.php) - soporte para datos de prueba.
- [app/backend/tests/Feature/Api/V1/PatchCommerceStatusTest.php](app/backend/tests/Feature/Api/V1/PatchCommerceStatusTest.php) - patrón de pruebas.
- [app/backend/tests/Feature/Api/V1/PatchCommerceVerificationTest.php](app/backend/tests/Feature/Api/V1/PatchCommerceVerificationTest.php) - patrón de pruebas.

**Verification**
1. Confirmar columnas nuevas en DB tras migration: terms_accepted_at y terms_accepted_version.
1. Probar endpoint con permiso correcto y validar persistencia en DB.
1. Probar sin permiso y esperar 403.
1. Probar payload inválido y esperar 422.
1. Probar id inexistente y esperar 404.
1. Correr tests de regresión de commerce patch endpoints.
1. Correr Pint.

**Decisions**
- Ruta confirmada: /api/v1/commerces/{id}/accept-terms (plural).
- Permiso confirmado: provider.commerces.accept-terms (nuevo, no reutilizar update).
- Alcance incluido: backend completo (DB, API, permisos, tests, OpenAPI).
- Alcance excluido: frontend y flujos UX de aceptación.

Si te parece bien este plan, en el siguiente paso puedo ejecutarlo por fases en ese mismo orden.
