# Cambios de permisos en endpoints de sucursales de comercio

Este documento detalla los ajustes de permisos que afectan los siguientes endpoints:

## Endpoints afectados

### 1. `/api/v1/commerce-branches`
- **Tipo:** Resource Controller (CRUD completo)
- **Acciones:**
  - `GET /api/v1/commerce-branches` (Listar sucursales)
  - `POST /api/v1/commerce-branches` (Crear sucursal)
  - `GET /api/v1/commerce-branches/{id}` (Ver sucursal)
  - `PUT/PATCH /api/v1/commerce-branches/{id}` (Actualizar sucursal)
  - `DELETE /api/v1/commerce-branches/{id}` (Eliminar sucursal)
- **Permisos requeridos:**
  - `provider.branches.index` (listar)
  - `provider.branches.create` (crear)
  - `provider.branches.show` (ver)
  - `provider.branches.update` (actualizar)
  - `provider.branches.delete` (eliminar)

### 2. `/api/v1/commerces/{commerce_id}/branches`
- **Tipo:** Endpoint personalizado para obtener sucursales por comercio
- **Acción:**
  - `GET /api/v1/commerces/{commerce_id}/branches` (Listar sucursales de un comercio específico)
- **Permiso requerido:**
  - `provider.branches.index`

## Resumen de ajustes
- Los endpoints de sucursales de comercio ahora requieren permisos explícitos para cada acción, definidos en el seeder `RolePermissionSeeder`.
- El permiso `provider.branches.index` es obligatorio tanto para el listado general como para el listado por comercio (`getBranchesByCommerceId`).
- Los permisos de creación, visualización, actualización y eliminación de sucursales están protegidos por sus respectivos permisos (`create`, `show`, `update`, `delete`).
- Si el usuario no tiene el permiso correspondiente, recibirá un error 403 Forbidden.

---

**Nota:** Verifica que los roles asignados a los usuarios incluyan los permisos necesarios para operar sobre estos endpoints. Los cambios garantizan mayor seguridad y trazabilidad en la gestión de sucursales de comercio.
