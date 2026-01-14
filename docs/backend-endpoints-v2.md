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

## ✅ Validación de Endpoints - Users

**✅ Validado:** 2026-01-14

### Endpoints Disponibles

Todos los endpoints necesarios para gestión de usuarios están **implementados y disponibles** en Laravel:

#### 1. GET /api/v1/users (list)
- **Estado:** ✅ Implementado
- **Controller:** `UserController@index`
- **Método:** GET
- **Autenticación:** Sanctum
- **Query params:**
  - `search` - Búsqueda por nombre, apellido o email
  - `role` - Filtrar por rol (nombre del rol)
  - `status` - Filtrar por estado ('A' activo, 'I' inactivo)
  - `per_page` - Paginación (default 15)
  - `page` - Número de página
- **Respuesta:** Paginada con `UserResource[]`
- **Estructura del recurso:**
  ```json
  {
    "id": 1,
    "name": "Juan",
    "last_name": "Pérez",
    "email": "juan.perez@example.com",
    "phone": "3001234567",
    "roles": ["admin", "user"],
    "status": "A",
    "created_at": "2023-01-01T12:00:00Z",
    "updated_at": "2023-01-01T12:00:00Z"
  }
  ```

#### 2. GET /api/v1/users/{id} (show)
- **Estado:** ✅ Implementado
- **Controller:** `UserController@show`
- **Método:** GET
- **Autenticación:** Sanctum
- **Parámetros:** `user_id` (int)
- **Respuesta:** `UserResource` con datos completos del usuario

#### 3. POST /api/v1/users (store)
- **Estado:** ✅ Implementado
- **Controller:** `UserController@store`
- **Método:** POST
- **Autenticación:** Sanctum
- **Request:** `UserRequest` con validaciones
- **Respuesta:** 201 Created con `UserResource`

#### 4. PUT /api/v1/users/{id} (update)
- **Estado:** ✅ Implementado
- **Controller:** `UserController@update`
- **Método:** PUT
- **Autenticación:** Sanctum
- **Parámetros:** `user_id` (int)
- **Request:** `UserRequest` con validaciones
- **Respuesta:** 200 OK con `UserResource` actualizado

#### 5. DELETE /api/v1/users/{id} (destroy)
- **Estado:** ✅ Implementado
- **Controller:** `UserController@destroy`
- **Método:** DELETE
- **Autenticación:** Sanctum
- **Parámetros:** `user_id` (int)
- **Respuesta:** 200 OK (soft delete)
- **Nota:** Usa soft deletes (`SoftDeletes` trait)

#### 6. PATCH /api/v1/users/{id}/status (toggle)
- **Estado:** ✅ Implementado
- **Controller:** `UserController@updateStatus`
- **Método:** PATCH
- **Autenticación:** Sanctum
- **Parámetros:** `user_id` (int)
- **Request:** `UserStatusRequest`
- **Body esperado:**
  ```json
  {
    "status": "A" // 'A' para activo, 'I' para inactivo
  }
  ```
- **Respuesta:** 200 OK con `UserResource` actualizado

#### 7. GET /api/v1/administrators (list admins)
- **Estado:** ✅ Implementado (bonus)
- **Controller:** `UserController@administrators`
- **Método:** GET
- **Autenticación:** Sanctum
- **Descripción:** Endpoint especial para obtener solo usuarios administradores
- **Respuesta:** Paginada con `UserResource[]` filtrados por rol admin

### Modelo de Datos Backend

**Tabla:** `users`

**Campos:**
- `id` (int, PK)
- `name` (string) - Nombre del usuario
- `last_name` (string) - Apellido del usuario
- `email` (string, unique) - Email del usuario
- `phone` (string) - Teléfono/celular
- `password` (string, hashed) - Contraseña hasheada
- `status` (string) - Estado: 'A' (activo) o 'I' (inactivo)
- `email_verified_at` (timestamp, nullable)
- `remember_token` (string, nullable)
- `created_at` (timestamp)
- `updated_at` (timestamp)
- `deleted_at` (timestamp, nullable) - Soft delete

**Relaciones:**
- `roles` - Spatie Permission (many-to-many)
- `permissions` - Spatie Permission (many-to-many)

**Sanitización automática:**
- `name` → Capitalizado
- `last_name` → Capitalizado
- `email` → Lowercase + trim
- `phone` → Limpiado (solo dígitos)

### Mapeo Frontend ↔ Backend

**Frontend (Usuario type):**
```typescript
interface Usuario {
  id: number;
  nombres: string;      // ← Backend: name
  apellidos: string;    // ← Backend: last_name
  celular: string;      // ← Backend: phone
  email: string;        // ← Backend: email
  perfil: string;       // ← Backend: roles[0] (primer rol)
  activo: boolean;      // ← Backend: status === 'A'
}
```

**Backend (UserResource):**
```json
{
  "id": 1,
  "name": "Juan",
  "last_name": "Pérez",
  "email": "juan@example.com",
  "phone": "3001234567",
  "roles": ["admin", "user"],
  "status": "A",
  "created_at": "2023-01-01T12:00:00Z",
  "updated_at": "2023-01-01T12:00:00Z"
}
```

### Conclusión

✅ **Todos los endpoints necesarios están implementados y funcionales.**

**Lista de verificación:**
- ✅ GET /api/v1/users (list con paginación y filtros)
- ✅ GET /api/v1/users/{id} (show individual)
- ✅ POST /api/v1/users (create)
- ✅ PUT /api/v1/users/{id} (update)
- ✅ DELETE /api/v1/users/{id} (soft delete)
- ✅ PATCH /api/v1/users/{id}/status (toggle estado)
- ✅ GET /api/v1/administrators (bonus para admins)

**Próximos pasos:**
1. ✅ Task #7 completada - Endpoints validados
2. ⏳ Task #4 - Crear módulo API `/lib/api/users.ts` con adaptadores
3. ⏳ Task #2 - Crear hook `useUserManagement` usando la API

---

## Notas

- Endpoints de autenticación y CRUD listados en el doc original se consideran implementados o validados; sólo se listan aquí los pendientes/bugs actuales.
- Si aparece un nuevo requerimiento, agregarlo en este archivo y marcar fecha/estado para mantener trazabilidad.
