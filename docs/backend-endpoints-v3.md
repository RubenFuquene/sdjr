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

## E) Contrato de Documentos en Detalle de Comercio (Validación/Admin)

### Problema detectado
El endpoint `GET /api/v1/commerces/{id}` actualmente no retorna los documentos cargados por el proveedor.
Esto bloquea el flujo de validación desde Admin, porque no hay datos para renderizar acciones de `Ver/Descargar`.

### Decisión de arquitectura
- **No exponer URLs directas públicas de S3/MinIO** en payloads de detalle.
- Mantener bucket privado y entregar acceso mediante **URL firmada por backend** (expiración corta).
- El endpoint de detalle debe retornar **metadata documental** + capacidad de descarga segura.

### Contrato propuesto (recomendado)

#### 1) `GET /api/v1/commerces/{id}`
Retorna el comercio con una colección de documentos (`documents`) orientada a UI y validación.

**Respuesta esperada (200):**
```json
{
  "status": true,
  "message": "Commerce retrieved successfully",
  "data": {
    "id": 16,
    "name": "Test",
    "tax_id": "5434463",
    "tax_id_type": "NIT",
    "email": "r@r.com",
    "phone": "2313512345",
    "is_active": true,
    "is_verified": false,
    "documents": [
      {
        "id": 42,
        "document_type": "CAMARA_COMERCIO",
        "original_name": "camara-comercio-2026.pdf",
        "mime_type": "application/pdf",
        "file_size_bytes": 2048000,
        "upload_status": "confirmed",
        "version_number": 1,
        "uploaded_at": "2026-02-22T14:30:00Z",
        "can_download": true,
        "download": {
          "mode": "signed_url",
          "url": null,
          "expires_at": null,
          "endpoint": "/api/v1/documents/42/download-url"
        }
      }
    ],
    "created_at": "2026-02-18T22:00:03.000000Z",
    "updated_at": "2026-02-18T22:00:03.000000Z"
  }
}
```

> Nota: `documents` debe incluir solo archivos con `upload_status = confirmed` para consumo de UI, salvo que se requiera explícitamente mostrar fallidos/pendientes en auditoría.

#### 2) `POST /api/v1/documents/{id}/download-url`
Genera URL firmada temporal para visualizar o descargar un documento privado.

**Request (opcional):**
```json
{
  "disposition": "inline"
}
```

`disposition` soportado:
- `inline` (ver en navegador)
- `attachment` (forzar descarga)

**Respuesta esperada (200):**
```json
{
  "status": true,
  "message": "Download URL generated successfully",
  "data": {
    "document_id": 42,
    "url": "https://s3.example.com/...signed...",
    "expires_at": "2026-02-22T14:36:00Z",
    "expires_in_seconds": 300
  }
}
```

### Campos mínimos por documento (para frontend)
- `id`
- `document_type` (enum backend)
- `original_name`
- `mime_type`
- `file_size_bytes`
- `upload_status`
- `version_number`
- `uploaded_at`
- `can_download`

### Seguridad y reglas
- Bucket privado (sin lectura pública).
- URL firmada con TTL corto (recomendado: 60-300 segundos).
- Verificar RBAC antes de firmar (admin, owner, perfiles autorizados).
- Registrar auditoría de descarga (quién, qué documento, cuándo).
- Validar que el documento pertenece al comercio solicitado (evitar IDOR).

### Compatibilidad con flujo de upload existente
Este contrato es compatible con:
- `POST /api/v1/documents/presigned`
- `POST /api/v1/documents/confirm`

El upload sigue siendo directo a S3/MinIO y este contrato solo define la fase de **consulta y descarga segura**.

### Criterios de aceptación (Jira)
- [ ] `GET /api/v1/commerces/{id}` incluye `documents[]` con metadata completa.
- [ ] No se exponen rutas internas del bucket ni objetos públicos.
- [ ] `POST /api/v1/documents/{id}/download-url` retorna URL firmada válida.
- [ ] URL firmada expira correctamente y no reutilizable fuera de TTL.
- [ ] Validación de permisos implementada (403 en acceso no autorizado).
- [ ] Frontend admin puede abrir/descargar documentos desde validación usando este contrato.

### Prioridad
**ALTA** - Bloqueador para flujo de validación documental de proveedores en Admin.

---

## F) Documentos Legales Estáticos (Términos, Privacidad, Etc.) - Opción Recomendada

### Contexto
Los documentos legales (Términos y Condiciones, Política de Privacidad, Acuerdos de Servicio) son **estáticos, mantenidos por la plataforma, y accesibles a todos los usuarios**.

A diferencia de documentos de proveedor (apartado E), estos **NO requieren firma de URLs** ni control de acceso RBAC granular.

### Comparativa: Documentos de Proveedor vs. Legales

| Aspecto | Documentos de Proveedor (E) | Documentos Legales (F) |
|--------|---------------------------|------------------------|
| **Propiedad** | Usuario/Proveedor privado | Plataforma pública |
| **Acceso** | Restringido (admin, owner, autorizado) | Público / Semi-público |
| **Cambio** | Frecuente (versionado) | Raro (estático, con versionado opcional) |
| **Storage** | S3/MinIO privado | S3/MinIO público O carpeta estática |
| **Firma URLs** | Requerida (TTL 60-300s) | NO requerida |
| **Auditoría** | Crítica (quién descargó cada versión) | Opcional (logs de lectura) |
| **Ejemplo** | RUT, registro cámara, certificaciones | Términos v2.0, Privacidad v1.3 |

### Decisión: Opción Recomendada = URLs Públicas Directas

**Razón:** Los documentos legales son estáticos y accesibles. No ganan seguridad con signing, pero agregan latencia.
- Usar **S3/MinIO público** con `application/pdf` MIME type.
- O servir desde **carpeta estática** (`/public/legal/`) sin necesidad de backend.
- Versionado en URL: `/legal/terms-v2.0.pdf` (no depender de ID de base de datos).

### Contrato propuesto

#### 1) `GET /api/v1/documents/legal`
Retorna lista de documentos legales disponibles con URLs directas (no privadas).

**Respuesta esperada (200):**
```json
{
  "status": true,
  "message": "Legal documents retrieved successfully",
  "data": [
    {
      "id": 1,
      "code": "TERMS_OF_SERVICE",
      "title": "Términos y Condiciones de Uso",
      "description": "Condiciones generales aplicables a todos los usuarios de la plataforma",
      "version": "2.0",
      "version_date": "2026-01-15",
      "effective_date": "2026-02-01",
      "url": "https://cdn.sumass.com/legal/terms-v2.0.pdf",
      "mime_type": "application/pdf",
      "file_size_bytes": 512000,
      "requires_acceptance": true,
      "acceptance_model": "digital_signature",
      "language": "es"
    },
    {
      "id": 2,
      "code": "PRIVACY_POLICY",
      "title": "Política de Privacidad",
      "description": "Cómo recopilamos, usamos y protegemos tus datos personales",
      "version": "1.5",
      "version_date": "2025-11-20",
      "effective_date": "2025-12-01",
      "url": "https://cdn.sumass.com/legal/privacy-v1.5.pdf",
      "mime_type": "application/pdf",
      "file_size_bytes": 384000,
      "requires_acceptance": false,
      "acceptance_model": null,
      "language": "es"
    },
    {
      "id": 3,
      "code": "SERVICE_AGREEMENT",
      "title": "Acuerdo de Servicio para Proveedores",
      "description": "Términos específicos para proveedores y comercios",
      "version": "1.0",
      "version_date": "2026-02-10",
      "effective_date": "2026-02-15",
      "url": "https://cdn.sumass.com/legal/provider-agreement-v1.0.pdf",
      "mime_type": "application/pdf",
      "file_size_bytes": 600000,
      "requires_acceptance": true,
      "acceptance_model": "digital_signature",
      "language": "es"
    }
  ]
}
```

#### 2) Almacenamiento recomendado

**Opción A: Carpeta estática (Recomendada para MVP)**
```
app/frontend/public/legal/
├── terms-v2.0.pdf
├── privacy-v1.5.pdf
└── provider-agreement-v1.0.pdf
```
- Sin backend = sin latencia
- URLs: `https://sumass.com/legal/terms-v2.0.pdf`
- BD solo almacena: metadata, versión, path relativo

**Opción B: S3/MinIO público (Escalable)**
```
s3://sumass-legal/
├── terms-v2.0.pdf
├── privacy-v1.5.pdf
└── provider-agreement-v1.0.pdf
```
- CDN global + replicas geográficas
- URLs: `https://cdn.sumass.com/legal/terms-v2.0.pdf`
- Mismo bucket de privado (con CORS público)

### Campos mínimos por documento legal
- `id` (para tracking/auditoría)
- `code` (enum: `TERMS_OF_SERVICE`, `PRIVACY_POLICY`, `SERVICE_AGREEMENT`)
- `title`
- `description`
- `version` (semver: `2.0`, `1.5`)
- `version_date` (cuándo se escribió)
- `effective_date` (cuándo entra en vigor)
- `url` (directo, público, firmado o no)
- `mime_type` (siempre `application/pdf` para MVP)
- `file_size_bytes` (opcional, optimización UX)
- `requires_acceptance` (boolean)
- `acceptance_model` (`digital_signature` | `checkbox` | null)

### Seguridad y reglas
- URLs públicas pero versionadas (`terms-v2.0.pdf` no conflicto con `terms-v3.0.pdf`).
- Cambios de contenido siempre crean nueva versión + nueva URL.
- No servir sin MIME type correcto (evitar ejecución).
- Opcional: Registrar auditoría de visualización (opcional para docs públicos).

### Flujo frontend esperado

**En `ProviderLegalTab`:**
```tsx
// 1. Fetch legal docs
const legalDocs = await fetch('/api/v1/documents/legal');

// 2. Render preview cards con urls directas
legalDocs.forEach(doc => {
  <a href={doc.url} target="_blank" rel="noopener">
    {doc.title} (v{doc.version})
  </a>
})

// 3. Si requires_acceptance=true → mostrar checkbox + firma digital
```

Ninguna signed URL, ningún endpoint de descarga temporal. Solo links directos.

### Compatibilidad con flujo de aceptación

Si backend necesita trackear "usuario X aceptó términos v2.0 en fecha Y":
```
POST /api/v1/user-acceptances
{
  "legal_document_id": 1,
  "acceptance_type": "digital_signature",
  "signature_data": "... base64 ..."
}
```

Pero puede ir en **tasks futuras** (no bloquea MVP).

### Criterios de aceptación (Jira)
- [ ] `GET /api/v1/documents/legal` retorna lista con URLs públicas accesibles.
- [ ] URLs son estables entre versiones (no sufren cambios).
- [ ] Documentos legales renderizados en `ProviderLegalTab` con links directos.
- [ ] Navegación a PDF funciona (se abre en navegador o descarga según navegador).
- [ ] MIME types correctos en servidor (evitar content-disposition: attachment sin intención).
- [ ] Versionado semántico consistente (`v2.0`, `v1.5`, etc.).

### Prioridad
**MEDIA** - No bloquea validación de proveedores, pero crítico para compliance/legal.

---
