# Endpoints Backend Pendientes - SDJR

## Contexto

Lista de endpoints faltantes en el backend Laravel que necesitamos para completar la funcionalidad del frontend. Estos serán implementados por Jerson Jiménez.

## Endpoints Existentes con Bugs

### GET /api/v1/roles
**Problema**: El campo `users_count` siempre devuelve 0, no cuenta correctamente los usuarios asignados a cada rol
**Query params**: `per_page` (paginación)
**Respuesta actual**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "admin",
      "description": "Administrator role",
      "permissions": ["admin.*"],
      "users_count": 0  // ❌ SIEMPRE 0
    }
  ]
}
```
**Respuesta esperada**:
```json
{
  "data": [
    {
      "id": 1,
      "name": "admin",
      "description": "Administrator role",
      "permissions": ["admin.*"],
      "users_count": 5  // ✅ CONTEO REAL
    }
  ]
}
```

## Endpoints de Autenticación

### 1. GET /api/v1/me

**Propósito**: Obtener información del usuario actualmente autenticado
**¿Necesario?**: Útil para mostrar info del usuario en el header/sidebar, verificar sesión activa
**Respuesta esperada**:

```json
{
  "id": 1,
  "name": "Admin User",
  "email": "admin@sumass.com",
  "roles": ["admin"],
  "permissions": ["admin.*"]
}
```

### 2. GET /api/v1/me/permissions

**Propósito**: Obtener permisos específicos del usuario autenticado
**Necesario**: Para el sistema de permisos frontend (Zustand store)
**Respuesta esperada**:

```json
{
  "permissions": [
    "admin.roles.view",
    "admin.roles.create",
    "admin.users.view",
    "admin.providers.view",
    "admin.analytics.view"
  ],
  "roles": ["admin", "super-admin"]
}
```

### 3. POST /api/v1/logout

**Propósito**: Destruir la sesión del usuario
**Necesario**: Para logout seguro desde frontend
**Payload**: Ninguno (usa token de auth)
**Respuesta esperada**:

```json
{
  "message": "Sesión cerrada exitosamente"
}
```

## Endpoints de Gestión (Panel Admin)

### 4. GET /api/v1/providers

**Propósito**: Listar proveedores con filtros y paginación
**Necesario**: Para tabla de proveedores en dashboard
**Query params**: `page`, `per_page`, `search`, `status`
**Respuesta esperada**:

```json
{
  "data": [
    {
      "id": 1,
      "nombreComercial": "Proveedor XYZ",
      "nit": "123456789",
      "representanteLegal": "Juan Pérez",
      "tipoEstablecimiento": "Restaurante",
      "telefono": "3001234567",
      "email": "contacto@proveedor.com",
      "ciudad": "Bogotá",
      "departamento": "Cundinamarca",
      "perfil": "Premium",
      "activo": true,
      "created_at": "2025-01-01T00:00:00Z"
    }
  ],
  "meta": {
    "current_page": 1,
    "per_page": 15,
    "total": 100
  }
}
```

### 5. GET /api/v1/users

**Propósito**: Listar usuarios del sistema
**Necesario**: Para tabla de usuarios en dashboard
**Query params**: `page`, `per_page`, `search`, `role`, `status`
**Respuesta esperada**: Similar a providers, con campos de usuario

### 6. GET /api/v1/administrators

**Propósito**: Listar administradores del sistema
**Necesario**: Para tabla de administradores en dashboard
**Query params**: `page`, `per_page`, `search`, `area`, `status`
**Respuesta esperada**: Similar a providers, con campos de admin

## Endpoints CRUD (Futuros)

### Roles

- PUT /api/v1/roles/{id} (actualizar)
- DELETE /api/v1/roles/{id} (eliminar)

### Providers

- POST /api/v1/providers
- PUT /api/v1/providers/{id}
- DELETE /api/v1/providers/{id}

### Users

- POST /api/v1/users
- PUT /api/v1/users/{id}
- DELETE /api/v1/users/{id}

### Administrators

- POST /api/v1/administrators
- PUT /api/v1/administrators/{id}
- DELETE /api/v1/administrators/{id}

## Consideraciones Técnicas

- **Autenticación**: Todos requieren Bearer token
- **Paginación**: Usar Laravel pagination estándar
- **Filtros**: Implementar search básico por nombre/email
- **Estados**: Campo `activo` para soft delete lógico
- **Permisos**: Validar permisos en backend antes de operaciones

## Prioridad de Implementación

1. **Alta**: /me/permissions, /logout (para sistema de permisos)
2. **Media**: /me, /providers, /users, /administrators (para completar dashboard)
3. **Baja**: Endpoints CRUD (para futuras funcionalidades)

## Notas para Jerson

- Mantener consistencia con el endpoint `/api/v1/roles` existente
- Usar resource classes para responses
- Implementar validation con Form Requests
- Agregar tests para cada endpoint
