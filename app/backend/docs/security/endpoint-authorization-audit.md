# Auditoría de autorización de endpoints (SCRUM-334) — v2

**Fecha v1:** 2026-07-14 · **Fecha v2:** 2026-08-06
**Alcance:** todos los endpoints bajo `Route::middleware(['auth:sanctum', ...])` en `routes/api.php`.
**Objetivo:** confirmar que cada endpoint valida, sobre el recurso al que accede, los **dos ejes** de
autorización a nivel de función.

## Metodología — dos ejes, no uno

| Eje | Pregunta | Cómo se valida |
|---|---|---|
| **Permiso** | ¿El usuario tiene el permiso Spatie correcto para esta acción? | `$user->can('provider.recurso.accion')` en `authorize()` |
| **Propiedad** | ¿El recurso específico sobre el que opera pertenece al usuario (o a su comercio)? | Ownership vía `AuthorizesCommerceOwnership` u otro chequeo explícito contra `owner_user_id` |

**La v1 de este documento (julio 2026) solo auditó el eje permiso.** Su conclusión — "no se encontraron
endpoints adicionales protegidos solo por `auth:sanctum` sin ningún `authorize()`" — era correcta para lo
que medía, pero dos de sus filas ✅ ("Comercios" y "Sucursales", marcadas "+ ownership") afirmaron más de
lo que el código realmente verificaba. SCRUM-287 (agosto 2026) destapó la primera grieta real: un
`commerce_id` aceptado del payload sin comprobar que perteneciera al usuario. Investigar ese hallazgo llevó
a una segunda pasada completa del eje propiedad (esta v2), que encontró **23 endpoints adicionales**
vulnerables al mismo patrón: `authorize()` valida el permiso Spatie correctamente, pero nunca la propiedad
del recurso sobre el que se opera.

Leyenda: ✅ OK · ⚠️ HUECO (antes de corregir) · — no aplica (recurso sin dueño, ej. catálogo global).

---

## Huecos corregidos — eje permiso (auditoría original, julio 2026)

| Endpoint | Controller@método | Antes | Corrección | Origen |
|---|---|---|---|---|
| `POST /documents/{id}/download-url` | `DocumentUploadController::downloadCommerceDocumentUrl` | Sin FormRequest — IDOR | `ShowDocumentDownloadUrlRequest` + ownership | SCRUM-315 |
| `DELETE /documents/{document}` | `DocumentUploadController::remove` | `provider.products.delete` (permiso ajeno) | `DestroyDocumentUploadRequest` con `provider.documents.delete` + ownership | SCRUM-316 |
| `DELETE /products/commerce/photos/{photo}` | `ProductController::removePhoto` | Permiso ajeno (mismo Request de 316) | `DestroyProductPhotoRequest` con `provider.photos.delete` + ownership | SCRUM-316 (ampliado) |
| `DELETE /commerce-branches/photos/{photo}` | `CommerceBranchController::removePhoto` | Permiso ajeno (mismo Request de 316) | `DestroyCommerceBranchPhotoRequest` con `provider.photos.delete` + ownership | SCRUM-316 (ampliado) |
| `GET /audit-logs` | `AuditLogController::index` | Sin FormRequest | `IndexAuditLogRequest` con `admin.audit_logs.index` | Auditoría 334 |
| `GET /audit-logs/{id}` | `AuditLogController::show` | Sin FormRequest | `ShowAuditLogRequest` con `admin.audit_logs.show` | Auditoría 334 |
| `GET /support-statuses/{id}` | `SupportStatusController::show` | Sin FormRequest | `ShowSupportStatusRequest` (permiso ya existía) | Auditoría 334 |
| `DELETE /support-statuses/{id}` | `SupportStatusController::destroy` | Sin FormRequest | `DeleteSupportStatusRequest` (permiso ya existía) | Auditoría 334 |
| `DELETE /countries/{id}` | `CountryController::destroy` | Sin FormRequest | `DeleteCountryRequest` (permiso ya existía) | Auditoría 334 |

---

## Huecos corregidos — eje propiedad (segunda pasada, agosto 2026)

**Origen:** SCRUM-287 destapó el primer caso investigando un bug funcional de edición de sucursal.
El patrón se replica idéntico en los 23 endpoints de esta sección: `authorize()` valida el permiso
Spatie, nunca si el recurso pertenece al usuario. Todos reutilizan el trait ya existente
`App\Traits\AuthorizesCommerceOwnership` (o el mismo patrón inline donde el trait no encaja).

### Representantes legales (SCRUM-287/334)

| Endpoint | Antes | Corrección |
|---|---|---|
| `GET /legal-representatives` | Solo permiso — listaba de todos los comercios | Acotado por `owner_user_id` (admin/superadmin sin acotar) |
| `GET /legal-representatives/{id}` | Solo permiso | Ownership vía `commerce_id` del representante |
| `POST /legal-representatives` | Solo permiso, `commerce_id` del payload sin validar | `AuthorizesCommerceOwnership` sobre el `commerce_id` enviado |
| `PUT /legal-representatives/{id}` | Solo permiso; `commerce_id` en el payload permitía reasignar | Ownership del representante existente; `commerce_id` retirado de `rules()` en update |
| `DELETE /legal-representatives/{id}` | Solo permiso | Ownership vía `commerce_id` del representante |

### Sucursales (SCRUM-287/334)

| Endpoint | Antes | Corrección |
|---|---|---|
| `GET /commerce-branches` | Solo permiso — listaba de todos los comercios | Acotado por `owner_user_id` |
| `GET /commerce-branches/{id}` | Solo permiso | Ownership vía `commerce` de la sucursal |
| `DELETE /commerce-branches/{id}` | Solo permiso — la más severa de este grupo, borraba sucursal ajena | Ownership vía `commerce` de la sucursal |
| `GET /commerces/{commerce_id}/branches` | Solo permiso — reutilizaba `IndexCommerceBranchRequest` sin ownership del `commerce_id` de ruta | Nuevo `IndexBranchesByCommerceRequest` dedicado (no se reutilizó el compartido con el índice general) |

### Catálogo por comercio (SCRUM-343)

| Endpoint | Antes | Corrección |
|---|---|---|
| `GET /products/commerce/{commerce_id}` | **Sin ningún FormRequest** — cualquier autenticado veía el catálogo de cualquier comercio | `ProductsByCommerceRequest`: `provider.products.index` + ownership |
| `GET /products/commerce/branch/{branch_id}` | **Sin ningún FormRequest** | `ProductsByCommerceBranchRequest`: `provider.products.index` + ownership derivada de la sucursal |

Decisión de producto (2026-08-06): privado, no semi-público. El único consumidor real (panel de
proveedor) siempre consulta su propio comercio; el browsing del cliente ya tiene `nearby/*` y `catalog/*`.

### Comercios (segunda pasada, agosto 2026)

| Endpoint | Antes | Corrección | Severidad |
|---|---|---|---|
| `DELETE /commerces/{id}` | Solo permiso — cualquier aliado borraba el comercio de otro | `AuthorizesCommerceOwnership` | **Crítica** |
| `PATCH /commerces/{id}/status` | Solo permiso — activaba/desactivaba comercio ajeno | `AuthorizesCommerceOwnership` | **Crítica** |
| `PATCH /commerces/{id}/verification` | Solo `provider.commerces.update` (el rol provider lo tiene) — permitía auto-verificación | Permiso nuevo `admin.commerces.verify`, exclusivo de admin/superadmin — **no** ownership, ver nota abajo | **Crítica** |
| `PATCH /commerces/{id}/accept-terms` | Solo permiso | `AuthorizesCommerceOwnership` | Alta |
| `GET /commerces/{id}` | Solo permiso | `AuthorizesCommerceOwnership` | Media |
| `GET /commerces/{commerce_id}/payout-methods` | Solo permiso — exponía cuentas bancarias de cualquier comercio | Ownership del `commerce_id` de ruta en `IndexCommercePayoutMethodRequest` | **Alta** |
| `POST /commerces/basic` | No validaba `commerce.owner_user_id` del payload contra el usuario autenticado | Validación explícita (mismo criterio que `StoreCommerceRequest`) | Media |

**Nota sobre `verification`:** este es el único hallazgo de todo el PR que *no* se resuelve con ownership.
Verificar un comercio es una acción de la plataforma sobre un tercero — si el dueño pudiera verificar su
propio comercio, el estado "verificado" pierde su significado. Se creó `admin.commerces.verify`, un
permiso que **no** se asigna al rol `provider`, en vez de reutilizar `provider.commerces.update`.

### Productos (segunda pasada, agosto 2026)

| Endpoint | Antes | Corrección |
|---|---|---|
| `GET /products/{id}` | Solo permiso | Ownership vía `product->commerce_id` |
| `DELETE /products/{id}` | Solo permiso — borraba producto ajeno | Ownership vía `product->commerce_id` |
| `PATCH /products/{id}/status` | Solo permiso | Ownership vía `product->commerce_id` |

`ShowProductRequest` y `DeleteProductRequest` se reutilizan también en `getPackageItems` y
`deletePackageItems` (mismo recurso `Product`, distinto nombre de route param: `product_package_id`) —
resuelto ampliando el lookup, no con una clase nueva.

### Usuarios de sucursal / branch leaders (segunda pasada, agosto 2026)

| Endpoint | Antes | Corrección |
|---|---|---|
| `GET /commerce-branch-users?commerce_id=` | Solo rol (`provider`/`admin`/`superadmin`), sin ownership — exponía PII (nombre, email, teléfono) de líderes de cualquier comercio | Ownership del `commerce_id` |
| `GET /commerce-branch-users/commerce-branch/{id}` | Solo rol, incluía `branch_leader` sin validar que fuera *esa* sucursal | Doble camino: dueño del comercio, o `branch_leader` asignado específicamente a esa sucursal |

Sin cobertura de tests previa para todo `CommerceBranchUserController` — creado `CommerceBranchUserListTest.php`.
Se encontró además un bug funcional preexistente (no de autorización) en `getCommerceUsers()`: columna
`commerce_id` ambigua en SQL tras un join, nunca detectada por falta de tests. Corregido por bloquear la
verificación del fix de ownership.

---

## Huecos identificados y NO corregidos (fuera de alcance)

| Endpoint / código | Motivo |
|---|---|
| `App\Traits\AuthorizesCommerceOwnership` — bypass de admin/superadmin por **rol** (`hasAnyRole`) en vez de por **permiso** | Regla ya escrita en `owasp.md` que el trait aún no cumple. Cambiarlo es transversal a todos sus consumidores (10+ Requests) y merece su propio PR con su propia corrida de suite — no se mezcla con el blindaje de este PR. Derivado a ticket nuevo (Tarea 5.1 del plan). |

## Hallazgo colateral (no es de autorización, ya documentado en julio)

`CommerceBranchController::confirmPhotoUpload`/`removePhoto` usan el modelo Eloquent `CommerceBranch::class`
en vez de `CommerceBranchPhoto::class`. Causa raíz probable de que SCRUM-273 siga fallando en retest.
No corregido (fuera del alcance de autorización).

---

## Endpoints verificados sin hueco (✅ OK en ambos ejes)

- **Auth/Me:** `login` (scope, SCRUM-325), `password/forgot`, `password/reset`, `me`, `me/permissions`, `logout`.
- **Registro público:** `provider/register`, `customer/register` (`authorize() = true`, intencional).
- **Nearby/Catalog:** públicos explícitos, fuera de `auth:sanctum`.
- **Parametrización** (countries, departments, cities, neighborhoods, banks, establishment-types, pqrs-types, priority-types, support-statuses): sin dueño, permiso `admin.params.*` dedicado.
- **Usuarios/Roles/Permisos:** permiso `admin.profiles.*` dedicado.
- **Comentarios de comercio:** CRUD completo — permiso `provider.comments.*` + `userCanAccessCommerce()`. Verificado en la segunda pasada, sin cambios.
- **Documentos legales:** `legal-documents` — permiso `admin.legal_documents.*`.
- **Categorías de producto:** catálogo **global**, sin `commerce_id` — no aplica el eje propiedad. Verificado en la segunda pasada.
- **Órdenes:** CRUD, `patchStatus`, `myOrders`, `commerceBranchOrders`, pago (`StoreOrderTransactionRequest`) — ya blindados con ownership (comprador o dueño del comercio) y auto-scoping por `user_id`. Verificado en la segunda pasada, sin cambios.
- **Documentos de proveedor (upload):** `presigned`/`confirm` — ya blindados en SCRUM-242.
- **Geocode:** proxy sin recurso de tenant.
- **Productos:** CRUD (store/update ya validaban ownership vía `ProductService::validateStoreRequest`), `package-items` CRUD, `patchBranchPublication`, `dismissAutoAdjustment` — ya blindados.

---

## Conclusión

De ~100 FormRequests auditados en total: **9 huecos** corregidos en julio (eje permiso), **2 derivados**
a SCRUM-343 (ahora resueltos) y **23 huecos adicionales** corregidos en agosto (eje propiedad — SCRUM-287
+ segunda pasada de SCRUM-334), incluida la corrección de mayor severidad de toda la auditoría
(`DELETE /commerces/{id}` sin ownership). Un hallazgo (`AuthorizesCommerceOwnership` bypass por rol) queda
derivado a ticket nuevo. Criterio de cierre de SCRUM-334 satisfecho: inventario con estado por endpoint en
ambos ejes (permiso y propiedad).
