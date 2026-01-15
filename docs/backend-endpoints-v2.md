# Endpoints Backend Pendientes - SDJR (v2)

## Contexto

Listado vigente de requerimientos backend a implementar por Jerson Jiménez. Fecha de corte: 2026-01-08. Sustituye el documento anterior para evitar confusión con items ya completados.

## Pendientes

### 1) PATCH /api/v1/roles/{id} — Status Update (PENDIENTE CRÍTICO)

**🚨 CRÍTICO - Bloqueador para feature de activación/desactivación de roles**

- **Propósito:** Endpoint para actualizar solo el estado de un rol (activo/inactivo)
- **Ruta:** `PATCH /api/v1/roles/{id}`
- **Body esperado:** `{ "status": "0" | "1" }`
- **Validaciones esperadas:**
  - `id` existente → 404 si no existe
  - `status` requerido, in:0,1 → 422 si inválido
  - Permiso `admin.profiles.roles.update` → 403 si sin permisos
- **Respuesta exitosa (200 OK):**
  ```json
  {
    "id": 1,
    "name": "Administrador",
    "description": "Rol de administrador",
    "status": "1",
    "permissions": {...},
    "users_count": 5
  }
  ```
- **Frontend:** Ya implementado en `use-role-management.ts::handleToggleRoleStatus()` con manejo robusto de errores HTTP
- **Nota:** El PUT genérico requiere todos los campos (name, description), por lo que no es apropiado para actualizaciones parciales
- **Prioridad:** ALTA - El frontend está 100% listo, solo falta que el backend implemente PATCH con validación parcial

### 2) DELETE /api/v1/roles/{id} ✅ IMPLEMENTADO

**✅ Status: COMPLETADO**

- El endpoint `DELETE /api/v1/roles/{id}` está implementado en `RoleController::destroy()`
- Responde con 200 OK (o código adecuado) según la lógica configurada
- **Próximo paso:** Validar que sea soft delete (baja lógica) en lugar de eliminación física

### 3) GET /api/v1/roles con parámetro `q` ❌ PENDIENTE

**⚠️ Status: PENDIENTE**

- Contexto: El endpoint `GET /api/v1/roles` ya implementa filtros `name`, `description`, `permission`
- **Requerimiento:** Agregar parámetro `q` para búsqueda global rápida (like en name + description)
- **Ejemplo esperado:** `GET /api/v1/roles?q=admin` o `GET /api/v1/roles?q=admin&permission=roles.create`

### 4) PATCH /api/v1/commerces/{id}/status ❌ PENDIENTE

**⚠️ Status: PENDIENTE**

- Similar al requerimiento #1, necesita PATCH para actualización parcial de estado
- Body esperado: `{ "is_active": true | false }` o `{ "status": "1" | "0" }`
- El PUT genérico no es apropiado por los campos requeridos

### 5) PATCH /api/v1/commerces/{id}/verification ❌ NO IMPLEMENTADO

**⚠️ Status: PENDIENTE**

- No se encontró endpoint para marcar/verificar proveedores
- **Requerimiento:** Body esperado `{ "is_verified": true | false }`

### 6) DELETE /api/v1/commerces/{id} — Error 500 🐛 BUG ABIERTO

**🐛 Bug reportado:** 2026-01-14

**⚠️ Status: IMPLEMENTADO PERO CON BUG**

- El endpoint existe en `CommerceController::destroy()`
- **Problema:** Devuelve 500 Internal Server Error en lugar de 404 cuando el commerce no existe
- **Causa:** `CommerceService::delete()` no captura correctamente `ModelNotFoundException`
- **Solución esperada:** Retornar 404 con mensaje amigable cuando commerce_id no existe


### 7) GET /api/v1/commerces/{id} — legal_representatives entrega array de arrays 🐛 BUG ABIERTO

**🐛 Bug reportado:** 2026-01-15

**⚠️ Status: IMPLEMENTADO PERO CON BUG**

- El endpoint existe y retorna datos, pero la estructura es incorrecta
- **Problema:** `legal_representatives` se devuelve como `[[{...}]]` en lugar de `[{...}]`
- **Solución esperada:** Remover el nesting innecesario en el Resource o transformer de Commerce
- **Impacto:** El frontend requiere desanidación manual para consumir los datos

## Resumen de Estado

| # | Endpoint | Status | Acción | Frontend |
|---|----------|--------|--------|----------|
| 1 | PATCH /api/v1/roles/{id} | ❌ Pendiente (CRÍTICO) | Implementar endpoint PATCH con validación parcial | ✅ Listo - Manejo de error 405 |
| 2 | DELETE /api/v1/roles/{id} | ✅ Implementado | Validar que sea soft delete | ✅ Funciona |
| 3 | GET /api/v1/roles?q=... | ❌ Pendiente | Agregar parámetro de búsqueda global | ⏳ Pendiente |
| 4 | PATCH /api/v1/commerces/{id}/status | ❌ Pendiente | Implementar endpoint PATCH con validación parcial | ⏳ Pendiente |
| 5 | PATCH /api/v1/commerces/{id}/verification | ❌ Pendiente | Implementar nuevo endpoint | ⏳ Pendiente |
| 6 | DELETE /api/v1/commerces/{id} | 🐛 Bug (500 error) | Capturar ModelNotFoundException → 404 | ⏳ Pendiente |
| 7 | GET /api/v1/commerces/{id} legal_representatives | 🐛 Bug (array anidado) | Remover nesting innecesario en Resource | ⏳ Pendiente |

## Notas

- Endpoints de autenticación y CRUD listados en el doc original se consideran implementados o validados; sólo se listan aquí los pendientes/bugs actuales.
- Si aparece un nuevo requerimiento, agregarlo en este archivo y marcar fecha/estado para mantener trazabilidad.
- **Fecha de revisión:** 2026-01-15
- **Patrón PATCH:** El frontend implementa manejo robusto de errores HTTP para endpoints PATCH. Cuando el backend no soporta PATCH (405), se muestra error amigable al usuario con referencia al documento de requerimientos.
