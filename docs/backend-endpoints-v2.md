# Endpoints Backend Pendientes - SDJR (v2)

## Contexto

Listado vigente de requerimientos backend a implementar por Jerson Jiménez. Fecha de corte: 2026-01-08. Sustituye el documento anterior para evitar confusión con items ya completados.

## Pendientes

### 1) PATCH /api/v1/roles/ — Status Update (PENDIENTE CRÍTICO)

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

### 2) DELETE /api/v1/roles/ ✅ IMPLEMENTADO

**✅ Status: COMPLETADO**

- El endpoint `DELETE /api/v1/roles/{id}` está implementado en `RoleController::destroy()`
- Responde con 200 OK (o código adecuado) según la lógica configurada
- **Próximo paso:** Validar que sea soft delete (baja lógica) en lugar de eliminación física

### 3) GET /api/v1/roles con parámetro `q` ❌ PENDIENTE

**⚠️ Status: PENDIENTE**

- Contexto: El endpoint `GET /api/v1/roles` ya implementa filtros `name`, `description`, `permission`
- **Requerimiento:** Agregar parámetro `q` para búsqueda global rápida (like en name + description)
- **Ejemplo esperado:** `GET /api/v1/roles?q=admin` o `GET /api/v1/roles?q=admin&permission=roles.create`

### 4) PATCH /api/v1/commerces//status ❌ PENDIENTE

**⚠️ Status: PENDIENTE**

- Similar al requerimiento #1, necesita PATCH para actualización parcial de estado
- Body esperado: `{ "is_active": true | false }` o `{ "status": "1" | "0" }`
- El PUT genérico no es apropiado por los campos requeridos

### 5) PATCH /api/v1/commerces//verification ❌ NO IMPLEMENTADO

**⚠️ Status: PENDIENTE**

- No se encontró endpoint para marcar/verificar proveedores
- **Requerimiento:** Body esperado `{ "is_verified": true | false }`

### 6) DELETE /api/v1/commerces/ — Error 500 🐛 BUG ABIERTO

**🐛 Bug reportado:** 2026-01-14

**⚠️ Status: IMPLEMENTADO PERO CON BUG**

- El endpoint existe en `CommerceController::destroy()`
- **Problema:** Devuelve 500 Internal Server Error en lugar de 404 cuando el commerce no existe
- **Causa:** `CommerceService::delete()` no captura correctamente `ModelNotFoundException`
- **Solución esperada:** Retornar 404 con mensaje amigable cuando commerce_id no existe

### 7) GET /api/v1/commerces/ — legal_representatives entrega array de arrays 🐛 BUG ABIERTO

**🐛 Bug reportado:** 2026-01-15

**⚠️ Status: IMPLEMENTADO PERO CON BUG**

- El endpoint existe y retorna datos, pero la estructura es incorrecta
- **Problema:** `legal_representatives` se devuelve como `[[{...}]]` en lugar de `[{...}]`
- **Solución esperada:** Remover el nesting innecesario en el Resource o transformer de Commerce
- **Impacto:** El frontend requiere desanidación manual para consumir los datos

### 8) GET /api/v1/commerces//branches — Listar sucursales de un comercio ❌ NO IMPLEMENTADO

**⚠️ Status: NO IMPLEMENTADO**

- **Propósito:** Obtener la lista de sucursales (Commerce Branch) de un comercio específico
- **Ruta esperada:** `GET /api/v1/commerces/{id}/branches`
- **Contexto:** La tabla `commerce_branches` existe en el diagrama ER (ERsumass.drawio) pero no hay modelo ni endpoints implementados
- **Esquema DB esperado:**
  ```sql
  commerce_branches:
    - id (PK)
    - commerce_id (FK)
    - department_id (FK)
    - city_id (FK)
    - neighborhood_id (FK)
    - name (varchar)
    - address (varchar)
    - latitude (float)
    - longitude (float)
    - phone (varchar)
    - email (varchar)
    - is_active (boolean)
    - created_at (datetime)
    - updated_at (datetime)
  ```
- **Respuesta esperada (200 OK):**
  ```json
  {
    "data": [
      {
        "id": 1,
        "commerce_id": 5,
        "name": "Sucursal Norte",
        "address": "Calle 123 #45-67",
        "department": "Cundinamarca",
        "city": "Bogotá",
        "neighborhood": "Chapinero",
        "latitude": 4.6097,
        "longitude": -74.0817,
        "phone": "3001234567",
        "email": "norte@comercio.com",
        "is_active": true,
        "created_at": "2026-01-18T10:00:00Z",
        "updated_at": "2026-01-18T10:00:00Z"
      }
    ]
  }
  ```
- **Validaciones esperadas:**
  - `commerce_id` debe existir → 404 si no existe
  - Permiso `admin.providers.view` → 403 si sin permisos
- **Frontend:** Requerido para renderizar la tab "Sucursales" en el modal de visualización de proveedores
- **Trabajo requerido:**
  1. Crear migración `create_commerce_branches_table`
  2. Crear modelo `CommerceBranch` con relaciones (belongsTo Commerce, Department, City, Neighborhood)
  3. Crear endpoint `GET /api/v1/commerces/{id}/branches` en `CommerceController`
  4. Crear Resource `CommerceBranchResource` para serializar respuesta
- **Prioridad:** MEDIA - Feature completa requiere también endpoints POST/PUT/DELETE para CRUD de sucursales

### 9) GET /api/v1/commerces//payout-methods — Información bancaria/métodos de pago ⚠️ NO IMPLEMENTADO EN ENDPOINT

**⚠️ Status: DATOS EXISTEN PERO SIN ENDPOINT DEDICATED**

- **Contexto:** El modelo `CommercePayoutMethod` existe y almacena información bancaria (tabla `commerce_payout_methods`)
- **Problema:** No hay endpoint específico para obtener los métodos de pago de un comercio
- **Datos disponibles en BD:**
  - `commerce_payout_methods` table con relaciones a:
    - `banks` (tabla auxiliar con id, name, code)
    - `users` (owner_id - propietario de la cuenta)
  - Campos:
    - `type`: enum (bank, paypal, crypto)
    - `bank_id`: FK a banks table
    - `account_type`: enum (savings, checking, other)
    - `account_number`: string (con máscara recomendada)
    - `is_primary`: boolean
    - `status`: char (0/1)
- **Respuesta esperada (200 OK):**
  ```json
  {
    "data": [
      {
        "id": 1,
        "commerce_id": 5,
        "type": "bank",
        "bank": {
          "id": 1,
          "name": "Banco de Bogotá",
          "code": "BOGOTA"
        },
        "account_type": "savings",
        "account_number": "****9876",
        "owner": {
          "id": 1,
          "name": "Juan García",
          "email": "juan@comercio.com"
        },
        "is_primary": true,
        "status": "1",
        "created_at": "2026-01-18T10:00:00Z",
        "updated_at": "2026-01-18T10:00:00Z"
      }
    ]
  }
  ```
- **Validaciones esperadas:**
  - `commerce_id` debe existir → 404 si no existe
  - Permiso `admin.providers.view` → 403 si sin permisos
  - Enmascarar `account_number` (mostrar solo últimos 4 dígitos) por seguridad
- **Frontend:** Requerido para:
  - Tab "Información Bancaria" en el modal de visualización de proveedores
  - Validación antes de permitir pagos/transferencias
  - Mostrar método primario en resumen de proveedor
- **Trabajo requerido:**
  1. Crear Resource `CommercePayoutMethodResource` (ya existe como **BankResource** pero no es específica del commerce)
  2. Crear endpoint `GET /api/v1/commerces/{id}/payout-methods` en `CommerceController`
  3. Asegurar enmascaramiento de `account_number` en la respuesta
  4. (Opcional) Endpoints POST/PUT/DELETE para CRUD de métodos de pago
- **Nota técnica:** Ya existe `CommercePayoutMethodResource` completa en el backend (con relaciones a Bank y Owner)
- **Prioridad:** MEDIA - Similar a sucursales, es información adicional del proveedor

### 10) GET /api/v1/banks — Listado de bancos disponibles ✅ EXISTE

**✅ Status: IMPLEMENTADO**

- **Endpoint:** `GET /api/v1/banks`
- **Propósito:** Obtener lista de bancos disponibles para seleccionar al agregar método de pago
- **Controlador:** `BankController`
- **Resource:** `BankResource` (con id, name, code, status)
- **Frontend:** Ya consume este endpoint (ver `BancoOption` en types/provider.ts)

### 11) GET /api/v1/commerces/{id}/documents — Documentos del comercio ⚠️ PARCIAL

**⚠️ Status: DATOS EXISTEN PERO INCOMPLETO**

- **Contexto:** La tabla `commerce_documents` existe y almacena documentos de comercios (tabla `commerce_documents`)
- **Estructura BD:** Modelo completo con campos para tipo, archivo, verificación, etc.
- **Datos disponibles:**
  - `commerce_documents` table con relaciones a:
    - `commerce` (FK)
    - `verified_by` (user que verifica - FK)
    - `uploaded_by` (user que sube - FK)
  - Campos:
    - `document_type`: enum (ej: ID_CARD, REGISTRATION, etc.)
    - `file_path`: ruta al archivo subido
    - `mime_type`: tipo de archivo (pdf, jpg, etc.)
    - `verified`: boolean (documentación verificada)
    - `uploaded_at`: timestamp de carga
    - `verified_at`: timestamp de verificación
- **Respuesta esperada (200 OK):**
  ```json
  {
    "data": [
      {
        "id": 1,
        "document_type": "CAMARA_COMERCIO",
        "file_path": "/uploads/documents/comercio_5_chamber_cert.pdf",
        "mime_type": "application/pdf",
        "verified": true,
        "uploaded_at": "2026-01-18T10:00:00Z",
        "verified_at": "2026-01-18T11:00:00Z"
      }
    ]
  }
  ```
- **Validaciones esperadas:**
  - `commerce_id` debe existir → 404 si no existe
  - Permiso `admin.providers.view` → 403 si sin permisos
- **Frontend:** Requerido para:
  - Tab "Documentos" en modal de proveedor
  - Mostrar documentos habilitadores (cédula de cámara, RUT, etc.)
- **Trabajo requerido:**
  1. Crear endpoint `GET /api/v1/commerces/{id}/documents` en `CommerceController`
  2. Resource `CommerceDocumentResource` ya existe (listo para usar)
  3. Definir enumeración de `document_type` permitidos (en Constants.php)
  4. (Opcional) Endpoints POST para subir nuevos documentos
- **Nota técnica:** Resource `CommerceDocumentResource` está completo en backend
- **Prioridad:** MEDIA - Información complementaria del proveedor

### 12) Documentos Legales de Plataforma (Términos, Privacidad, Contrato) ❌ NO IMPLEMENTADO

**❌ Status: NO EXISTE EN BD NI ENDPOINTS**

- **Contexto:** El frontend espera 3 documentos legales estáticos:
  - Términos y Condiciones
  - Política de Privacidad
  - Contrato de Prestación de Servicios
- **Problema:** No hay tabla en BD para estos documentos, no hay endpoints
- **Frontend actual:**
  - ProviderLegalTab espera acceso a estos documentos
  - Links hardcodeados en componente (placeholder: `/legal/terminos-y-condiciones`, etc.)
- **Propuesta técnica:**
  - **Opción A (Simple):** Servir documentos legales como HTML estático desde `/public/legal/` 
    - No requiere BD
    - Frontend accede directamente a URLs
    - Fácil mantenimiento sin backend
  - **Opción B (Completa):** Crear tabla `legal_documents` en BD
    - Permite gestión administrativa de documentos
    - Histórico de cambios
    - Versioning de términos/políticas
  - **Opción C (Hibrida):** Endpoints que devuelven HTML de documentos desde storage
    - Flexible para actualizaciones
    - Control backend
    - Sin tabla de BD (almacenamiento en S3 o local)
- **Campos esperados (si se elige opción B/C):**
  ```sql
  legal_documents:
    - id (PK)
    - type: enum ('terms', 'privacy', 'service_contract')
    - title: varchar
    - content: longtext (HTML)
    - version: int
    - status: enum ('draft', 'active', 'archived')
    - effective_date: datetime
    - created_at: datetime
    - updated_at: datetime
  ```
- **Endpoint esperado (si opción B/C):**
  ```
  GET /api/v1/legal-documents/{type}
  GET /api/v1/legal-documents (listar todos)
  ```
- **Respuesta esperada:**
  ```json
  {
    "data": {
      "type": "terms",
      "title": "Términos y Condiciones",
      "content": "<h1>Términos...</h1>",
      "version": 1,
      "effective_date": "2026-01-01T00:00:00Z"
    }
  }
  ```
- **Frontend:** Requerido para:
  - Tab "Información Legal" en modal de proveedor
  - Mostrar links a documentos legales
  - Página de aceptación durante registro
  - Footer con links a políticas
- **Decidir primero:**
  1. ¿Cuál es la propuesta de almacenamiento? (estático, BD, cloud storage)
  2. ¿Necesita versionado/histórico de cambios?
  3. ¿Panel admin para gestionar documentos?
- **Prioridad:** BAJA - MVP puede usar documentos estáticos en `/public/legal/`

## Resumen de Estado

| #  | Endpoint                                         | Status                  | Acción                                            | Frontend     |
| -- | ------------------------------------------------ | ----------------------- | -------------------------------------------------- | ------------ |
| 1  | PATCH /api/v1/roles/{id}                         | ❌ Pendiente (CRÍTICO) | Implementar endpoint PATCH con validación parcial | ⏳ Pendiente |
| 2  | DELETE /api/v1/roles/{id}                        | ✅ Implementado         | Validar que sea soft delete                        | ✅ Funciona  |
| 3  | GET /api/v1/roles?q=...                          | ❌ Pendiente            | Agregar parámetro de búsqueda global             | ⏳ Pendiente |
| 4  | PATCH /api/v1/commerces/{id}/status              | ❌ Pendiente            | Implementar endpoint PATCH con validación parcial | ⏳ Pendiente |
| 5  | PATCH /api/v1/commerces/{id}/verification        | ❌ Pendiente            | Implementar nuevo endpoint                         | ⏳ Pendiente |
| 6  | DELETE /api/v1/commerces/{id}                    | 🐛 Bug (500 error)      | Capturar ModelNotFoundException → 404             | ⏳ Pendiente |
| 7  | GET /api/v1/commerces/{id} legal_representatives | 🐛 Bug (array anidado)  | Remover nesting innecesario en Resource            | ⏳ Pendiente |
| 8  | GET /api/v1/commerces/{id}/branches              | ❌ Pendiente            | Crear modelo, migración, endpoint y Resource      | ⏳ Pendiente |
| 9  | GET /api/v1/commerces/{id}/payout-methods        | ❌ Pendiente            | Crear endpoint (Resource ya existe)                | ⏳ Pendiente |
| 10 | GET /api/v1/banks                                | ✅ Implementado         | N/A - Usar en formularios de método de pago       | ✅ Funciona  |
| 11 | GET /api/v1/commerces/{id}/documents             | ❌ Pendiente            | Crear endpoint (Resource ya existe)                | ⏳ Pendiente |
| 12 | Documentos Legales (Términos, Privacidad, etc.)  | ❌ Pendiente            | Decidir almacenamiento (estático/BD/cloud)        | ⏳ Pendiente |

## Notas

- Endpoints de autenticación y CRUD listados en el doc original se consideran implementados o validados; sólo se listan aquí los pendientes/bugs actuales.
- Si aparece un nuevo requerimiento, agregarlo en este archivo y marcar fecha/estado para mantener trazabilidad.
- **Fecha de revisión:** 2026-01-18
- **Patrón PATCH:** El frontend implementa manejo robusto de errores HTTP para endpoints PATCH. Cuando el backend no soporta PATCH (405), se muestra error amigable al usuario con referencia al documento de requerimientos.
- **Sucursales:** La tabla `commerce_branches` está diseñada en el diagrama ER pero no implementada en backend. Requiere trabajo completo: migración, modelo, endpoints CRUD y Resources.
- **Métodos de Pago:** La tabla `commerce_payout_methods` y modelo existen. El Resource `CommercePayoutMethodResource` está listo. Solo falta crear el endpoint `GET /api/v1/commerces/{id}/payout-methods`.
- **Documentos:** La tabla `commerce_documents` existe con Resource completro. Solo requiere endpoint `GET /api/v1/commerces/{id}/documents`.
- **Documentos Legales:** No están contemplados en el diseño actual. Requiere decisión técnica sobre almacenamiento (estático en `/public/legal/`, BD, o cloud storage).
