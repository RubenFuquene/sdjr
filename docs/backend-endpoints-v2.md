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

### 3) DELETE /api/v1/roles/{id} (pendiente por definir)

- Observación: no hay endpoint en Swagger ni en el controller actual.
- Decidir alcance: eliminar físico vs. baja lógica (status=0). En línea con Spatie, preferimos baja lógica y conservación de permisos históricos.
- Si se implementa, exigir permiso `admin.profiles.roles.delete` (o equivalente) y responder 200 con confirmación. Si se opta por baja lógica, podría reutilizar el PATCH de estado; de lo contrario, implementar `DELETE` explícito.

### 4) GET /api/v1/roles — parámetro `q` para búsqueda global (nuevo)

- Contexto: GET /api/v1/roles ya implementa filtros `name`, `description`, `permission` en el método `index()` del RoleController. Estos filtros funcionan y están disponibles.
- Necesidad: agregar un parámetro `q` para búsqueda rápida en nombre/descripción (y opcionalmente permisos) usado por el buscador del frontend.
- Propuesta: nuevo query param `q` que aplique like sobre name+description, conviviendo con los filtros específicos existentes. Ejemplo: `GET /api/v1/roles?q=admin` o `GET /api/v1/roles?q=admin&permission=roles.create`.

## Notas

- Endpoints de autenticación y CRUD listados en el doc original se consideran implementados o validados; sólo se listan aquí los pendientes/bugs actuales.
- Si aparece un nuevo requerimiento, agregarlo en este archivo y marcar fecha/estado para mantener trazabilidad.
