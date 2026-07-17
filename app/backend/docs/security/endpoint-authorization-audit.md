# Auditoría de autorización de endpoints (SCRUM-334)

**Fecha:** 2026-07-14
**Alcance:** todos los endpoints bajo `Route::middleware(['auth:sanctum', ...])` en `routes/api.php`.
**Objetivo:** confirmar que cada endpoint valida el **permiso** correcto (no solo autenticación) vía
un FormRequest con `authorize()` (o Gate/Policy equivalente).

Leyenda: ✅ OK (permiso correcto validado) · ⚠️ HUECO (sin `authorize()` real o permiso incorrecto).

---

## Huecos encontrados

| Endpoint | Controller@método | Antes | Corrección (este PR) | Origen |
|---|---|---|---|---|
| `POST /documents/{id}/download-url` | `DocumentUploadController::downloadCommerceDocumentUrl` | Sin FormRequest — IDOR | `ShowDocumentDownloadUrlRequest` + ownership | SCRUM-315 |
| `DELETE /documents/{document}` | `DocumentUploadController::remove` | `provider.products.delete` (permiso ajeno) | `DestroyDocumentUploadRequest` con `provider.documents.delete` + ownership | SCRUM-316 |
| `DELETE /products/commerce/photos/{photo}` | `ProductController::removePhoto` | `provider.products.delete` (mismo Request de 316) | `DestroyProductPhotoRequest` con `provider.photos.delete` + ownership | SCRUM-316 (ampliado) |
| `DELETE /commerce-branches/photos/{photo}` | `CommerceBranchController::removePhoto` | `provider.products.delete` (mismo Request de 316) | `DestroyCommerceBranchPhotoRequest` con `provider.photos.delete` + ownership | SCRUM-316 (ampliado) |
| `GET /audit-logs` | `AuditLogController::index` | Sin FormRequest — cualquier autenticado | `IndexAuditLogRequest` con `admin.audit_logs.index` | Auditoría 334 |
| `GET /audit-logs/{id}` | `AuditLogController::show` | Sin FormRequest — cualquier autenticado | `ShowAuditLogRequest` con `admin.audit_logs.show` | Auditoría 334 |
| `GET /support-statuses/{id}` | `SupportStatusController::show` | Sin FormRequest | `ShowSupportStatusRequest` con `admin.params.support_statuses.show` (permiso ya existía) | Auditoría 334 |
| `DELETE /support-statuses/{id}` | `SupportStatusController::destroy` | Sin FormRequest | `DeleteSupportStatusRequest` con `admin.params.support_statuses.delete` (permiso ya existía) | Auditoría 334 |
| `DELETE /countries/{id}` | `CountryController::destroy` | Sin FormRequest | `DeleteCountryRequest` con `admin.params.countries.delete` (permiso ya existía) | Auditoría 334 |

## Huecos identificados pero NO corregidos en este PR

| Endpoint | Motivo |
|---|---|
| `GET /products/commerce/{commerce_id}` | `ProductController::byCommerce` — sin `authorize()`. No está claro si el catálogo de productos por comercio se diseñó como semi-público (browsing) o si debería exigir permiso/ownership. Requiere decisión de producto. **Derivado a ticket.** |
| `GET /products/commerce/branch/{branch_id}` | `ProductController::byCommerceBranch` — mismo caso que el anterior. |

## Hallazgo colateral (no es de autorización)

`CommerceBranchController::confirmPhotoUpload`/`removePhoto` usan el modelo Eloquent `CommerceBranch::class`
en vez de `CommerceBranchPhoto::class` (tabla dedicada `commerce_branch_photos` existente). La tabla
`commerce_branches` no tiene columnas `upload_token`/`upload_status`, así que cualquier uso real de estos
endpoints lanzaría una excepción SQL — el flujo de foto de sucursal está roto de fábrica. Es la causa raíz
más probable de que **SCRUM-273** siga fallando en retest. Documentado como comentario técnico en ese
ticket; no se corrige aquí (fuera del alcance de autorización). El fix de ownership de este PR para
`DestroyCommerceBranchPhotoRequest` se resuelve deliberadamente sobre el modelo actual (`CommerceBranch`),
coherente con el bug preexistente.

---

## Endpoints verificados sin hueco (✅ OK)

Cobertura completa por dominio — todos usan un FormRequest con `authorize()` validando permiso Spatie
(y ownership vía `AuthorizesCommerceOwnership` o equivalente cuando el recurso es de un comercio):

- **Auth/Me:** `login` (scope, SCRUM-325), `password/forgot`, `password/reset`, `me`, `me/permissions`, `logout` (auto-scoped al usuario autenticado, sin permiso adicional necesario).
- **Registro público:** `provider/register`, `customer/register` (`authorize() = true`, intencional).
- **Nearby:** `nearby/branches`, `nearby/products` (`authorize() = true`, público explícito, fuera de `auth:sanctum`).
- **Parametrización (countries, departments, cities, neighborhoods, banks, establishment-types, pqrs-types, priority-types, support-statuses):** index/show/store/update con permiso `admin.params.*` dedicado por acción.
- **Usuarios/Roles/Permisos:** `users` CRUD + `updateStatus` + `administrators`, `roles` CRUD + `assign-roles-permissions` + `patchStatus`, `permissions` index/store — todos con permiso `admin.profiles.*` dedicado.
- **Comercios:** `commerces` CRUD, `patchStatus`, `patchVerification`, `acceptTerms`, `getBranchesByCommerceId`, `getPayoutMethodsByCommerceId`, `myCommerce` — permiso `provider.commerces.*` + ownership (bypass admin por rol, ver Observaciones del plan).
- **Sucursales:** `commerce-branches` CRUD, `myFavorites`, `confirmPhotoUpload` — permiso `provider.branches.*` / `provider.photos.upload` + ownership.
- **Comentarios de comercio:** CRUD completo con permiso `provider.comments.*` + `userCanAccessCommerce()`.
- **Documentos legales:** `legal-documents` index/showByType/store — permiso `admin.legal_documents.*`.
- **Representantes legales:** CRUD completo — permiso `provider.legal_representatives.*`.
- **Usuarios de sucursal (branch leaders):** index/store/assign/remove/showBranchUsers — por rol (`provider`/`admin`/`superadmin`/`branch_leader`).
- **Geocode:** `search`, `reverse` — permiso `provider.geocode.*`.
- **Productos:** CRUD, `patchStatus`, `package-items` CRUD, `confirmPhotoUpload` — permiso `provider.products.*` (algunos vía `ProductService::validateStoreRequest`).
- **Categorías de producto:** CRUD completo — permiso `provider.product_categories.*`.
- **Órdenes:** CRUD, `patchStatus`, `myOrders` (auto-scoped a `$request->user()->id`), `commerceBranchOrders` — permiso `customer.orders.*`/`provider.orders.*` + ownership (comprador o dueño del comercio).
- **Documentos de proveedor (upload):** `presigned` (Store), `confirm` (Patch) — permiso `provider.documents.upload` + ownership (ya blindado en SCRUM-242).

---

## Conclusión

De ~100 FormRequests auditados, se encontraron **9 huecos corregidos** en este PR y **2 huecos derivados**
a ticket por requerir decisión de producto. No se encontraron endpoints adicionales protegidos solo por
`auth:sanctum` sin ningún `authorize()`.
