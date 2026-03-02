# ADR 002 — Products: discrepancia de payload de sucursales entre create/update

## Estado
Accepted (MVP Provider Products Modal)

## Fecha
2026-03-02

## Contexto
El formulario unificado de productos (`create/edit`) necesita enviar la sucursal seleccionada por el proveedor.

Contrato backend actual:
- Create (`POST /api/v1/products`): espera `commerce_branch_ids[]`
- Update (`PUT /api/v1/products/{id}`): espera `commerce_branches[]`

Para un mismo concepto de negocio (sucursales del producto), la API usa dos nombres distintos.

## Decisión
En frontend se implementan **mappers separados por operación** para aislar la discrepancia del resto de la UI:

- `mapProductFormToCreatePayload(...)`
  - envía `commerce_branch_ids: [selectedBranchId]`

- `mapProductFormToUpdatePayload(...)`
  - envía `commerce_branches: [selectedBranchId]`

La UI mantiene selección de sucursal como **single branch** en MVP.
Siempre se envía como array para cumplir contrato backend.

## Consecuencias
### Positivas
- No bloquea el avance del modal create/edit.
- Evita condicionales de contrato distribuidos en componentes.
- Reduce riesgo de errores al centralizar el mapeo en capa API.

### Negativas
- Se mantiene deuda técnica temporal por inconsistencia de contrato.
- Requiere atención cuando backend unifique naming.

## Plan de salida
Cuando backend unifique contrato (ideal: `commerce_branch_ids[]` en create/update):
1. Actualizar mappers a un único naming.
2. Eliminar rama legacy en update.
3. Actualizar tests/documentación de integración.

## Referencias
- `docs/backend-endpoints-v3.md` (sección H: solicitud Jira de unificación)
- `app/backend/app/Http/Requests/Api/V1/StoreProductRequest.php`
- `app/backend/app/Http/Requests/Api/V1/UpdateProductRequest.php`
