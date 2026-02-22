# Solicitud Backend: Usuarios (/api/v1/users) y Administradores

## Contexto
Este documento unifica los requerimientos del endpoint de administradores y nuevas observaciones del endpoint de usuarios.

---

## A) Solicitud: /api/v1/administrators -> ✅ IMPLEMENTADO

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

## B) Observacion: POST /api/v1/users (roles en payload) -> ✅ CORREGIDO

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

## C) Observacion: PUT /api/v1/users/{id} (status) -> ⚠️ No se detectó el error, se da como solucionado

- `UpdateUserPayload` usa `status: 'A' | 'I'`.
- `CreateUserPayload` usa `status: '1' | '0'`.

**Recomendacion:** unificar formato de status en backend y documentarlo (idealmente `'1' | '0'`).

---

## D) Solicitud: GET /api/v1/me/commerce - Obtener comercio del usuario autenticado

### Contexto
- El panel del proveedor necesita cargar el comercio asociado al usuario autenticado cuando navega a `/provider/basic-info`.
- Actualmente solo existen:
  - `GET /api/v1/commerces` - Lista todos los comercios (paginado)
  - `GET /api/v1/commerces/{id}` - Obtiene por ID específico
- No hay forma de obtener el comercio por `owner_user_id` del usuario autenticado.

### Endpoint solicitado
**Ruta:** `GET /api/v1/me/commerce` o `GET /api/v1/commerces/mine`

### Lógica esperada
1. Obtener el usuario autenticado (`auth()->user()`)
2. Buscar el comercio donde `owner_user_id = auth()->id()`
3. Retornar el comercio completo con relaciones necesarias

### Respuesta esperada (200 OK - Comercio existe)
```json
{
  "status": true,
  "message": "Commerce retrieved successfully",
  "data": {
    "id": 5,
    "commercial_name": "Restaurante El Buen Sabor",
    "document_type": "nit",
    "document_number": "900123456-7",
    "establishment_type": "restaurant",
    "phone": "3001234567",
    "email": "contacto@elbuen sabor.com",
    "department_id": 11,
    "city_id": 149,
    "neighborhood": "Chapinero",
    "main_address": "Calle 45 #23-10",
    "legal_representatives": [
      {
        "id": 1,
        "first_name": "Juan",
        "last_name": "Pérez",
        "document_type": "cc",
        "document_number": "1234567890",
        "document_file": "path/to/document.pdf"
      }
    ],
    "documents": {
      "commerce_chamber": "path/to/chamber.pdf",
      "identity": "path/to/id.pdf"
    },
    "observations": "Observaciones del comercio",
    "owner_user_id": 42,
    "is_active": true,
    "is_verified": false,
    "created_at": "2026-02-18T10:00:00Z",
    "updated_at": "2026-02-18T10:00:00Z"
  }
}
```

### Respuesta esperada (200 OK - Comercio no existe)
```json
{
  "status": true,
  "message": "No commerce found for authenticated user",
  "data": null
}
```

### Validaciones
- Usuario autenticado requerido (middleware `auth:sanctum`)
- No requiere permisos especiales (el usuario solo ve su propio comercio)

### Relaciones a incluir
- `legalRepresentatives` (representantes legales)
- `documents` (documentos del comercio)
- `department`, `city` (información de ubicación)

### Uso en frontend
- Al navegar a `/provider/basic-info`, el frontend llamará a este endpoint
- Si `data !== null`, pre-llenará el formulario con los datos existentes (modo edición)
- Si `data === null`, mostrará el formulario vacío (modo creación)

### Controlador sugerido
- **Opción 1:** Agregar método `myCommerce()` en `MeController`
- **Opción 2:** Agregar método `mine()` en `CommerceController`

### Prioridad
**ALTA** - Bloqueador para flujo de edición de datos del proveedor

---

