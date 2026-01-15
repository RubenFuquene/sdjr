# Endpoints Backend Pendientes - SDJR (v2)

## Contexto

Listado vigente de requerimientos backend a implementar por Jerson Jiménez. Fecha de corte: 2026-01-08. Sustituye el documento anterior para evitar confusión con items ya completados.

## Pendientes

### 1) PATCH /api/v1/roles//status (nuevo)

- Propuesta: endpoint dedicado para activar/inactivar rol.
- Body esperado: `{ "status": "0" | "1" }` (aceptar boolean si se prefiere en backend, pero responder como string/entero consistente con el resto del modelo).
- Validaciones: permiso `admin.profiles.roles.update` o equivalente; `id` existente; `status` requerido e in:0,1.
- Respuesta: rol con estado actualizado (usar `RoleResource`), o al menos `{ "message": "Role status updated" }` + status code 200.
- Notas: evita exigir `name` y `description` en updates parciales y mantiene semántica clara.

### 2) GET /api/v1/roles — users_count incorrecto (bug abierto)

- Problema: `users_count` sigue devolviendo 0 en la colección.
- Esperado: conteo real de usuarios por rol en el listado.
- Aceptación: respuesta incluye `users_count` correcto para cada item (ejemplo: 5 cuando hay 5 usuarios con el rol) y mantiene la paginación/filters actuales.

### 3) DELETE /api/v1/roles/ (pendiente por definir)

- Observación: no hay endpoint en Swagger ni en el controller actual.
- Decidir alcance: eliminar físico vs. baja lógica (status=0). En línea con Spatie, preferimos baja lógica y conservación de permisos históricos.
- Si se implementa, exigir permiso `admin.profiles.roles.delete` (o equivalente) y responder 200 con confirmación. Si se opta por baja lógica, podría reutilizar el PATCH de estado; de lo contrario, implementar `DELETE` explícito.

### 4) GET /api/v1/roles — parámetro `q` para búsqueda global (nuevo)

- Contexto: GET /api/v1/roles ya implementa filtros `name`, `description`, `permission` en el método `index()` del RoleController. Estos filtros funcionan y están disponibles.
- Necesidad: agregar un parámetro `q` para búsqueda rápida en nombre/descripción (y opcionalmente permisos) usado por el buscador del frontend.
- Propuesta: nuevo query param `q` que aplique like sobre name+description, conviviendo con los filtros específicos existentes. Ejemplo: `GET /api/v1/roles?q=admin` o `GET /api/v1/roles?q=admin&permission=roles.create`.

### 5) PATCH /api/v1/commerces/status

- Propuesta: endpoint dedicado para activar/inactivar comercio (proveedor).
- Body esperado: `{ "is_active": true | false }` (aceptar `{ "status": "1" | "0" }` opcionalmente, pero responder de forma consistente con el modelo actual).
- Validaciones: permiso `provider.commerces.update`; `id` existente; `is_active` requerido, boolean.
- Respuesta: `CommerceResource` con estado actualizado o `{ "message": "Commerce status updated" }` + status code 200.
- Notas: evita exigir `name`, `address`, etc. en updates parciales (ver punto 7).

### 6) PATCH /api/v1/commerces/`<id>`/verification

- Propuesta: endpoint para marcar/verificar proveedor.
- Body esperado: `{ "is_verified": true | false }`.
- Validaciones: permiso `provider.commerces.update` y reglas de negocio para verificación.
- Respuesta: `CommerceResource` actualizado o `{ "message": "Commerce verification updated" }` + status code 200.

### 7) DELETE /api/v1/commerces/`<id>` — Error 500 (BUG)

**🐛 Bug reportado:** 2026-01-14

- **Problema:** Al intentar eliminar un commerce existente (ej: ID 13), el endpoint devuelve 500 Internal Server Error.
- **Error actual:**
  ```json
  {
    "status": false,
    "message": "Error deleting commerce",
    "errors": {
      "exception": "No query results for model [App\\Models\\Commerce] 13"
    }
  }
  ```
- **Causa raíz:** `CommerceService::delete()` usa `findOrFail()` que lanza `ModelNotFoundException`, la cual no está siendo capturada correctamente y se propaga como error 500.
- **Código actual:**
  ```php
  public function delete(int $commerce_id): void
  {
      DB::transaction(function () use ($commerce_id) {
          $commerce = Commerce::findOrFail($commerce_id); // ❌ Lanza excepción no controlada
          $commerce->delete();
      });
  }
  ```
- **Solución esperada:**
  1. Capturar `ModelNotFoundException` en el controller o service y retornar 404 con mensaje amigable.
  2. O cambiar a `find()` y validar manualmente si el registro existe antes de intentar eliminarlo.
- **Respuesta actual correcta (cuando existe):** 204 No Content.
- **Impacto:** El frontend muestra error genérico al usuario en lugar de mensaje claro "Proveedor no encontrado".

## Notas

- Endpoints de autenticación y CRUD listados en el doc original se consideran implementados o validados; sólo se listan aquí los pendientes/bugs actuales.
- Si aparece un nuevo requerimiento, agregarlo en este archivo y marcar fecha/estado para mantener trazabilidad.
