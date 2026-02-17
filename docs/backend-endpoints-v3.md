# Solicitud Backend: Usuarios (/api/v1/users) y Administradores

## Contexto
Este documento unifica los requerimientos del endpoint de administradores y nuevas observaciones del endpoint de usuarios.

---

## A) Solicitud: /api/v1/administrators

### Estado actual
- Controlador: `UserController::administrators()`
- Logica: `User::with('roles')->get()->filter(...)`
- Respuesta: `successResponse(UserResource::collection($users))`
- Swagger: no declara query params ni paginacion

### 1) Agregar paginacion
- Query params: `page`, `per_page`
- Usar `paginatedResponse(...)` para respuesta estandar

### 2) Agregar filtros server-side
- `search`: busca en `name` o `last_name` (match parcial en cualquiera) y tambien en `email`
- `status`: filtra por `status` ('1' | '0')
- `role`: filtra por nombre de rol (ademas de admin/superadmin)

### 3) Respuesta esperada (200)
```json
{
  "status": true,
  "message": "Administrators retrieved successfully",
  "data": [
    {
      "id": 1,
      "name": "Maria",
      "last_name": "Perez",
      "email": "maria@sumass.com",
      "phone": "3001234567",
      "roles": ["admin"],
      "status": "1",
      "created_at": "2026-02-16T12:00:00Z",
      "updated_at": "2026-02-16T12:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 3,
    "per_page": 15,
    "to": 15,
    "total": 45
  },
  "links": {
    "first": "...",
    "last": "...",
    "prev": null,
    "next": "..."
  }
}
```

### Notas
- Si el backend agrega campo `area`, el frontend dejara de usar el fallback "Sin area".
- Se recomienda filtrar en query (no en coleccion) para evitar cargas grandes.

---

## B) Observacion: POST /api/v1/users (roles en payload)

### Duda actual
El frontend esta enviando el rol como arreglo en el campo `roles`:
```json
{
  "roles": ["admin"]
}
```

### Resultado de revision (backend)
- `UserRequest` **no valida** el campo `roles`.
- `UserService::create()` y `UserService::update()` solo hacen `User::create/update($data)`.
- `User::$fillable` no incluye `roles`.
➡️ **Conclusión:** hoy el backend **ignora** el campo `roles` y no asigna roles al usuario.

### Propuesta de contrato (frontend actual)
```json
{
  "name": "Juan",
  "last_name": "Perez",
  "email": "juan@sumass.com",
  "phone": "3001234567",
  "password": "temp123",
  "password_confirmation": "temp123",
  "status": "1",
  "roles": ["admin"]
}
```

### Accion solicitada
- **Backend (recomendado):** soportar `roles: string[]`.
  - Validar `roles` y `roles.*` (exists en tabla roles)
  - En `UserService::create()` ejecutar `$user->syncRoles($roles)`
  - En `UserService::update()` permitir actualizar roles si vienen en payload
- **Frontend:** mantener `roles: string[]` (ya implementado)

---

## C) Observacion: PUT /api/v1/users/{id} (status)

- `UpdateUserPayload` usa `status: 'A' | 'I'`.
- `CreateUserPayload` usa `status: '1' | '0'`.

**Recomendacion:** unificar formato de status en backend y documentarlo (idealmente `'1' | '0'`).

---

