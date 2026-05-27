# Plan de Implementación: Sistema de Usuarios de Sucursales (Commerce Branch Users)

**Fecha de Creación:** 11 de Mayo 2026  
**Versión:** 1.0  
**Estado:** Pendiente de Aprobación

---

## Tabla de Contenidos
1. [Resumen Ejecutivo](#resumen-ejecutivo)
2. [Requerimientos Funcionales](#requerimientos-funcionales)
3. [Arquitectura de Base de Datos](#arquitectura-de-base-de-datos)
4. [Roles y Permisos](#roles-y-permisos)
5. [Endpoints a Crear](#endpoints-a-crear)
6. [Endpoints a Modificar](#endpoints-a-modificar)
7. [Notificaciones por Email](#notificaciones-por-email)
8. [Modelos y Relaciones](#modelos-y-relaciones)
9. [Servicios](#servicios)
10. [Validaciones y Reglas de Negocio](#validaciones-y-reglas-de-negocio)
11. [Tests Unitarios y de Integración](#tests-unitarios-y-de-integración)
12. [Documentación](#documentación)
13. [Plan de Ejecución](#plan-de-ejecución)

---

## 1. Resumen Ejecutivo

### Objetivo
Implementar un sistema que permita asociar uno o múltiples usuarios con rol `branch_leader` a las sucursales de un comercio (CommerceBranch), habilitando la gestión descentralizada de sucursales.

### Alcance
- Creación de tabla pivote `commerce_branch_users`
- Nuevo rol `branch_leader` con permisos específicos
- Endpoints para gestión de usuarios de sucursales
- Sistema de notificaciones por email para nuevos usuarios y asignaciones
- Integración con sistema de reset de contraseña para nuevos usuarios
- Tests completos y documentación actualizada

### Flujo de Usuario
1. **Owner crea sucursal** → Puede asignar usuarios existentes del commerce o crear nuevos
2. **Listar usuarios disponibles** → Muestra usuarios del commerce con rol branch_leader
3. **Asignar usuario existente** → Se envía email de confirmación de asignación
4. **Crear nuevo usuario** → Se crea con rol branch_leader, se envía email con token para establecer contraseña

---

## 2. Requerimientos Funcionales

### RF-01: Gestión de Tabla Pivote
- ✅ Crear tabla `commerce_branch_users` con relaciones M:N
- ✅ Incluir campos: `commerce_id`, `commerce_branch_id`, `user_id`
- ✅ Registrar timestamps para auditoría
- ✅ Soft deletes para mantener historial

### RF-02: Rol Branch Leader
- ✅ Crear rol `branch_leader` en RolePermissionSeeder
- ✅ Asignar permisos:
  - `provider.products.show` (ver productos)
  - `provider.branches.update` (actualizar sucursal)
- ✅ Permitir roles múltiples (branch_leader + provider)

### RF-03: Listar Usuarios del Commerce
- ✅ Endpoint GET que liste usuarios asignados al commerce
- ✅ Filtrar por commerce_id
- ✅ Mostrar solo usuarios con rol branch_leader
- ✅ Incluir información de sucursales asignadas

### RF-04: Crear Nuevo Usuario Branch Leader
- ✅ Endpoint POST para crear usuario
- ✅ Validar campos: nombre, apellido, email, teléfono (opcional)
- ✅ Asignar rol branch_leader automáticamente
- ✅ Generar token de establecimiento de contraseña
- ✅ Enviar email de bienvenida con token

### RF-05: Asignar Usuario a Sucursal
- ✅ Al crear CommerceBranch, recibir campo `user_id`
- ✅ Registrar relación en `commerce_branch_users`
- ✅ Validar que usuario pertenezca al commerce
- ✅ Permitir asignación a múltiples sucursales

### RF-06: Notificaciones
- ✅ Email para nuevos usuarios (con token newPassword)
- ✅ Email para usuarios existentes (confirmación de asignación)
- ✅ Queue: 'emails'
- ✅ Usar plantilla profesional

### RF-07: Establecimiento de Contraseña
- ✅ Reutilizar tabla `password_reset_tokens`
- ✅ Redirigir a ruta frontend `/newPassword`
- ✅ Reutilizar endpoint `POST /api/v1/password/reset`
- ✅ Token con expiración estándar (60 minutos)

### RF-08: Permisos del Owner
- ✅ Owner puede listar usuarios de sus sucursales
- ✅ Owner puede crear nuevos branch_leaders
- ✅ Owner puede asignar/desasignar usuarios a sucursales
- ✅ Owner puede ver todas las asignaciones de su commerce

### RF-09: Auditoría
- ✅ Registrar creación de usuarios branch_leader
- ✅ Registrar asignaciones a sucursales
- ✅ Registrar desasignaciones
- ✅ Usar AuditLogService existente

### RF-10: Seguridad de Autenticación
- ✅ Proteger endpoint de login contra usuarios sin contraseña
- ✅ Validación explícita de password NULL en AuthService
- ✅ Prevenir ataques de timing para detectar usuarios sin contraseña
- ✅ Mantener mismo mensaje de error (no revelar información)
- ✅ Tests de seguridad específicos para este caso

---

## 3. Arquitectura de Base de Datos

### 3.1 Nueva Tabla: `commerce_branch_users`

```php
Schema::create('commerce_branch_users', function (Blueprint $table) {
    $table->id();
    $table->foreignId('commerce_id')->constrained('commerces')->onDelete('cascade');
    $table->foreignId('commerce_branch_id')->constrained('commerce_branches')->onDelete('cascade');
    $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
    $table->timestamps();
    $table->softDeletes();
    
    // Índices
    $table->index(['commerce_id', 'commerce_branch_id']);
    $table->index(['commerce_id', 'user_id']);
    $table->index('user_id');
    
    // Constraint único para evitar duplicados
    $table->unique(['commerce_branch_id', 'user_id'], 'unique_branch_user');
});
```

**Justificación de commerce_id:**
- Facilita queries directas de usuarios por comercio
- Mejora performance en consultas de listado
- Mantiene integridad referencial explícita
- Simplifica lógica de negocio

### 3.2 Diagrama de Relaciones

```
Commerce (1) ----< (N) CommerceBranch
    |                        |
    |                        |
    +--------(N)-------------+
                |
          commerce_branch_users (Tabla Pivote)
                |
                +-----------> User (N)
```

### 3.3 Migración

**Archivo:** `database/migrations/2026_05_11_000000_create_commerce_branch_users_table.php`

---

## 4. Roles y Permisos

### 4.1 Nuevo Rol: `branch_leader`

**Ubicación:** `database/seeders/RolePermissionSeeder.php`

```php
// Agregar después del rol 'provider'
$branchLeaderRole = Role::create(['name' => 'branch_leader', 'guard_name' => 'sanctum']);
```

### 4.2 Permisos Asignados

| Permiso | Descripción | Justificación |
|---------|-------------|---------------|
| `provider.products.show` | Ver productos del commerce | Permite consultar inventario de la sucursal |
| `provider.branches.update` | Actualizar información de sucursal | Permite gestionar horarios, fotos, datos básicos |

### 4.3 Permisos Adicionales a Crear (si no existen)

Verificar existencia de:
- `provider.branches.update` (ya existe)
- `provider.products.show` (ya existe)

### 4.4 Actualización del Seeder

```php
// En RolePermissionSeeder::run()

// 6. Branch Leader Role (NUEVO)
$branchLeaderRole = Role::firstOrCreate(
    ['name' => 'branch_leader', 'guard_name' => 'sanctum']
);

$branchLeaderPermissions = [
    'provider.products.show',
    'provider.branches.update',
];

foreach ($branchLeaderPermissions as $permissionName) {
    $permission = Permission::firstOrCreate([
        'name' => $permissionName,
        'guard_name' => 'sanctum'
    ]);
    $branchLeaderRole->givePermissionTo($permission);
}
```

---

## 5. Endpoints a Crear

### 5.1 Listar Usuarios del Commerce

**Endpoint:** `GET /api/v1/commerces/{commerce_id}/users`  
**Middleware:** `auth:sanctum`, `throttle:authenticated`  
**Permisos:** `provider.commerces.show` (owner) o `admin.profiles.users.index` (admin)

**Request:**
```
GET /api/v1/commerces/123/users
```

**Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 45,
      "name": "Juan",
      "last_name": "Pérez",
      "email": "juan.perez@example.com",
      "phone": "3001234567",
      "status": "A",
      "roles": ["branch_leader"],
      "assigned_branches": [
        {
          "id": 10,
          "name": "Sucursal Norte",
          "assigned_at": "2026-05-10T10:00:00Z"
        },
        {
          "id": 12,
          "name": "Sucursal Sur",
          "assigned_at": "2026-05-11T14:30:00Z"
        }
      ]
    }
  ],
  "message": "Usuarios del comercio obtenidos exitosamente"
}
```

**Validaciones:**
- Commerce existe
- Usuario autenticado es owner del commerce o admin
- Retornar solo usuarios con rol `branch_leader`

**Controller:** `CommerceBranchUserController@index`  
**Request:** `GetCommerceBranchUsersRequest`  
**Resource:** `CommerceBranchUserResource`  
**Service:** `CommerceBranchUserService@getCommerceUsers()`

---

### 5.2 Crear Nuevo Usuario Branch Leader

**Endpoint:** `POST /api/v1/commerces/{commerce_id}/users`  
**Middleware:** `auth:sanctum`, `throttle:authenticated`  
**Permisos:** `provider.commerces.create` (owner) o `admin.profiles.users.create` (admin)

**Request:**
```json
{
  "name": "María",
  "last_name": "González",
  "email": "maria.gonzalez@example.com",
  "phone": "3009876543",
  "commerce_branch_id": 10
}
```

**Validaciones:**
- `name`: required, string, max:255
- `last_name`: required, string, max:255
- `email`: required, email, unique:users,email
- `phone`: nullable, string, regex:/^[0-9]{10}$/
- `commerce_branch_id`: required, exists:commerce_branches,id
- CommerceBranch debe pertenecer al commerce_id
- Usuario autenticado debe ser owner del commerce

**Response (201):**
```json
{
  "status": "success",
  "data": {
    "id": 46,
    "name": "María",
    "last_name": "González",
    "email": "maria.gonzalez@example.com",
    "phone": "3009876543",
    "status": "A",
    "roles": ["branch_leader"],
    "assigned_branches": [
      {
        "id": 10,
        "name": "Sucursal Norte",
        "assigned_at": "2026-05-11T15:00:00Z"
      }
    ],
    "password_token_sent": true
  },
  "message": "Usuario creado exitosamente. Se ha enviado un correo electrónico para establecer la contraseña."
}
```

**Proceso Interno:**
1. Validar datos
2. Crear usuario (sin contraseña)
3. Asignar rol `branch_leader`
4. Generar token de password_reset_tokens
5. Crear relación en commerce_branch_users
6. Enviar notificación `BranchLeaderWelcomeNotification`
7. Registrar en audit_logs
8. Retornar respuesta

**Controller:** `CommerceBranchUserController@store`  
**Request:** `StoreCommerceBranchUserRequest`  
**Resource:** `CommerceBranchUserResource`  
**Service:** `CommerceBranchUserService@createAndAssign()`  
**Notification:** `BranchLeaderWelcomeNotification` (NUEVA)

---

### 5.3 Asignar Usuario Existente a Sucursal

**Endpoint:** `POST /api/v1/commerce-branches/{branch_id}/users`  
**Middleware:** `auth:sanctum`, `throttle:authenticated`  
**Permisos:** `provider.branches.update` (owner) o `admin.profiles.users.update` (admin)

**Request:**
```json
{
  "user_id": 45
}
```

**Validaciones:**
- `user_id`: required, exists:users,id
- Usuario debe tener rol `branch_leader`
- Usuario debe pertenecer al mismo commerce
- No debe estar ya asignado a esta sucursal

**Response (200):**
```json
{
  "status": "success",
  "data": {
    "commerce_branch_id": 12,
    "commerce_branch_name": "Sucursal Centro",
    "user": {
      "id": 45,
      "name": "Juan",
      "last_name": "Pérez",
      "email": "juan.perez@example.com"
    },
    "assigned_at": "2026-05-11T16:00:00Z"
  },
  "message": "Usuario asignado a la sucursal exitosamente"
}
```

**Proceso Interno:**
1. Validar datos
2. Verificar que usuario pertenece al commerce
3. Crear relación en commerce_branch_users
4. Enviar notificación `BranchAssignmentNotification` (NUEVA)
5. Registrar en audit_logs
6. Retornar respuesta

**Controller:** `CommerceBranchUserController@assignToBranch`  
**Request:** `AssignUserToBranchRequest`  
**Service:** `CommerceBranchUserService@assignUserToBranch()`  
**Notification:** `BranchAssignmentNotification` (NUEVA)

---

### 5.4 Desasignar Usuario de Sucursal

**Endpoint:** `DELETE /api/v1/commerce-branches/{branch_id}/users/{user_id}`  
**Middleware:** `auth:sanctum`, `throttle:authenticated`  
**Permisos:** `provider.branches.update` (owner) o `admin.profiles.users.delete` (admin)

**Response (200):**
```json
{
  "status": "success",
  "message": "Usuario desasignado de la sucursal exitosamente"
}
```

**Validaciones:**
- Usuario debe estar asignado a la sucursal
- Usuario autenticado debe ser owner del commerce

**Controller:** `CommerceBranchUserController@removeFromBranch`  
**Service:** `CommerceBranchUserService@removeUserFromBranch()`

---

### 5.5 Listar Usuarios de una Sucursal

**Endpoint:** `GET /api/v1/commerce-branches/{branch_id}/users`  
**Middleware:** `auth:sanctum`, `throttle:authenticated`  
**Permisos:** `provider.branches.show` o `admin.profiles.users.index`

**Response (200):**
```json
{
  "status": "success",
  "data": [
    {
      "id": 45,
      "name": "Juan",
      "last_name": "Pérez",
      "email": "juan.perez@example.com",
      "phone": "3001234567",
      "assigned_at": "2026-05-10T10:00:00Z"
    }
  ],
  "message": "Usuarios de la sucursal obtenidos exitosamente"
}
```

**Controller:** `CommerceBranchUserController@getBranchUsers`  
**Service:** `CommerceBranchUserService@getBranchUsers()`

---

## 6. Endpoints a Modificar

### 6.1 Crear Sucursal (CommerceBranch)

**Endpoint:** `POST /api/v1/commerce-branches`  
**Estado Actual:** Crea sucursal sin usuarios asignados

**Modificación Requerida:**

**Request (nuevo campo):**
```json
{
  "commerce_id": 123,
  "name": "Sucursal Oeste",
  "address": "Calle 123 #45-67",
  "department_id": 1,
  "city_id": 10,
  "neighborhood_id": 100,
  "phone": "3001234567",
  "email": "oeste@commerce.com",
  "latitude": 4.6097,
  "longitude": -74.0817,
  "user_id": 45  // NUEVO CAMPO (opcional)
}
```

**Validación Adicional:**
- `user_id`: nullable, exists:users,id
- Si se provee user_id, validar que:
  - Usuario tiene rol `branch_leader`
  - Usuario pertenece al commerce (existe en commerce_branch_users con ese commerce_id)

**Proceso Interno Actualizado:**
1. Validar datos (incluido user_id)
2. Crear CommerceBranch
3. **NUEVO:** Si user_id se provee:
   - Crear relación en commerce_branch_users
   - Enviar notificación BranchAssignmentNotification
   - Registrar en audit_logs
4. Retornar respuesta

**Archivos a Modificar:**
- `app/Http/Requests/StoreCommerceBranchRequest.php` - Agregar validación user_id
- `app/Services/CommerceBranchService.php` - Agregar lógica de asignación
- `app/Http/Controllers/CommerceBranchController.php` - Llamar servicio de asignación

---

### 6.2 Actualizar Sucursal (CommerceBranch)

**Endpoint:** `PUT /api/v1/commerce-branches/{id}`

**Modificación Sugerida (Opcional):**
Permitir cambiar el usuario asignado principal (si se requiere concepto de "líder principal")

**Por ahora:** No modificar, usar endpoint de asignación/desasignación

---

## 7. Notificaciones por Email

### 7.1 BranchLeaderWelcomeNotification (NUEVA)

**Ubicación:** `app/Notifications/BranchLeaderWelcomeNotification.php`

**Propósito:** Email para usuarios recién creados con token para establecer contraseña

**Variables:**
- `$user` - Usuario creado
- `$branchName` - Nombre de la sucursal
- `$token` - Token de password_reset

**Subject:** "Bienvenido a Ñapa App - Líder de Sucursal"

**Contenido:**
```
Hola {nombre} {apellido},

Te damos la bienvenida a Ñapa App. Has sido designado como líder de la sucursal "{nombre_sucursal}".

Para comenzar a gestionar tu sucursal, necesitas establecer tu contraseña haciendo clic en el siguiente enlace:

{frontend_url}/newPassword?token={token}&email={email}

Este enlace expirará en 60 minutos.

Una vez que hayas establecido tu contraseña, podrás:
- Gestionar los productos de tu sucursal
- Actualizar la información de la sucursal

Si tienes alguna pregunta, no dudes en contactarnos.

¡Bienvenido a bordo!

El equipo de Ñapa App
```

**Características:**
- Implementa `ShouldQueue`
- Queue: 'emails'
- Incluye manejo de errores gracefully

---

### 7.2 BranchAssignmentNotification (NUEVA)

**Ubicación:** `app/Notifications/BranchAssignmentNotification.php`

**Propósito:** Email para usuarios existentes asignados a una nueva sucursal

**Variables:**
- `$user` - Usuario asignado
- `$branchName` - Nombre de la sucursal
- `$commerceName` - Nombre del comercio

**Subject:** "Asignación a Sucursal - Ñapa App"

**Contenido:**
```
Hola {nombre} {apellido},

Te informamos que has sido asignado como líder de la sucursal "{nombre_sucursal}" del comercio "{nombre_comercio}".

Ahora puedes gestionar esta sucursal desde tu panel de administración:

{frontend_url}/provider/dashboard

Recuerda que como líder de sucursal puedes:
- Gestionar los productos de la sucursal
- Actualizar la información de la sucursal

Si tienes alguna pregunta, no dudes en contactarnos.

¡Éxitos en tu gestión!

El equipo de Ñapa App
```

**Características:**
- Implementa `ShouldQueue`
- Queue: 'emails'
- Sin token (usuario ya tiene contraseña)

---

### 7.3 Archivos de Notificación

**Estructura de Archivos:**
```
app/Notifications/
├── BranchLeaderWelcomeNotification.php  // NUEVO
├── BranchAssignmentNotification.php     // NUEVO
├── WelcomeUserNotification.php          // EXISTENTE (referencia)
└── ResetPasswordNotification.php        // EXISTENTE (referencia)
```

---

## 8. Modelos y Relaciones

### 8.1 Nuevo Modelo: CommerceBranchUser

**Ubicación:** `app/Models/CommerceBranchUser.php`

**Características:**
```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class CommerceBranchUser extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'commerce_id',
        'commerce_branch_id',
        'user_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Relación con Commerce
     */
    public function commerce(): BelongsTo
    {
        return $this->belongsTo(Commerce::class);
    }

    /**
     * Relación con CommerceBranch
     */
    public function commerceBranch(): BelongsTo
    {
        return $this->belongsTo(CommerceBranch::class);
    }

    /**
     * Relación con User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

---

### 8.2 Actualizar Modelo: Commerce

**Archivo:** `app/Models/Commerce.php`

**Agregar Relación:**
```php
/**
 * Usuarios asignados a sucursales del comercio
 */
public function commerceBranchUsers(): HasMany
{
    return $this->hasMany(CommerceBranchUser::class);
}

/**
 * Usuarios únicos con rol branch_leader en el comercio
 */
public function branchLeaders(): BelongsToMany
{
    return $this->belongsToMany(
        User::class,
        'commerce_branch_users',
        'commerce_id',
        'user_id'
    )->distinct();
}
```

---

### 8.3 Actualizar Modelo: CommerceBranch

**Archivo:** `app/Models/CommerceBranch.php`

**Agregar Relación:**
```php
/**
 * Usuarios asignados a esta sucursal
 */
public function commerceBranchUsers(): HasMany
{
    return $this->hasMany(CommerceBranchUser::class);
}

/**
 * Usuarios con rol branch_leader asignados
 */
public function branchLeaders(): BelongsToMany
{
    return $this->belongsToMany(
        User::class,
        'commerce_branch_users',
        'commerce_branch_id',
        'user_id'
    )->withTimestamps();
}
```

---

### 8.4 Actualizar Modelo: User

**Archivo:** `app/Models/User.php`

**Agregar Relaciones:**
```php
/**
 * Asignaciones a sucursales
 */
public function commerceBranchUsers(): HasMany
{
    return $this->hasMany(CommerceBranchUser::class);
}

/**
 * Sucursales donde es branch_leader
 */
public function assignedBranches(): BelongsToMany
{
    return $this->belongsToMany(
        CommerceBranch::class,
        'commerce_branch_users',
        'user_id',
        'commerce_branch_id'
    )->withTimestamps();
}

/**
 * Comercios donde es branch_leader
 */
public function commercesAsBranchLeader(): BelongsToMany
{
    return $this->belongsToMany(
        Commerce::class,
        'commerce_branch_users',
        'user_id',
        'commerce_id'
    )->distinct();
}

/**
 * Verificar si es branch_leader
 */
public function isBranchLeader(): bool
{
    return $this->hasRole('branch_leader');
}
```

---

## 9. Servicios

### 9.1 Nuevo Servicio: CommerceBranchUserService

**Ubicación:** `app/Services/CommerceBranchUserService.php`

**Métodos Principales:**

#### `getCommerceUsers(int $commerceId): Collection`
Lista todos los usuarios branch_leader del comercio con sus sucursales asignadas.

#### `createAndAssign(array $data, int $commerceId, int $branchId): User`
Crea un nuevo usuario, le asigna rol branch_leader, genera token y envía email.

**Pasos:**
1. Crear usuario con UserService
2. Asignar rol branch_leader (RoleService)
3. Generar token password_reset (PasswordResetService)
4. Crear registro en commerce_branch_users
5. Enviar BranchLeaderWelcomeNotification
6. Registrar en audit_logs (AuditLogService)
7. Retornar usuario

#### `assignUserToBranch(int $userId, int $branchId): CommerceBranchUser`
Asigna un usuario existente a una sucursal.

**Pasos:**
1. Validar que usuario tiene rol branch_leader
2. Validar que usuario pertenece al commerce
3. Validar que no esté ya asignado
4. Crear registro en commerce_branch_users
5. Enviar BranchAssignmentNotification
6. Registrar en audit_logs
7. Retornar CommerceBranchUser

#### `removeUserFromBranch(int $userId, int $branchId): bool`
Desasigna un usuario de una sucursal (soft delete).

**Pasos:**
1. Buscar registro en commerce_branch_users
2. Soft delete
3. Registrar en audit_logs
4. Retornar true/false

#### `getBranchUsers(int $branchId): Collection`
Lista usuarios asignados a una sucursal específica.

#### `validateUserBelongsToCommerce(int $userId, int $commerceId): bool`
Valida que un usuario pertenezca al comercio (existe en commerce_branch_users).

**Dependencias:**
- UserService
- RoleService
- PasswordResetService
- AuditLogService
- Notification dispatcher

---

### 9.2 Actualizar Servicio: UserService

**Archivo:** `app/Services/UserService.php`

**Nuevo Método:**

#### `createUserWithoutPassword(array $data): User`
Creates a user without password (for branch_leaders who will set it later).

**SECURITY NOTE:** Users without password cannot login until they set one.
The AuthService explicitly validates NULL passwords to prevent security issues.

```php
/**
 * Create a user without password (for branch_leaders who will set it later).
 * 
 * SECURITY NOTE: Users without password cannot login until they set one.
 * The AuthService explicitly validates NULL passwords to prevent security issues.
 *
 * @param array $data User data (name, last_name, email, phone)
 * @return User The created user instance
 */
public function createUserWithoutPassword(array $data): User
{
    return User::create([
        'name' => $data['name'],
        'last_name' => $data['last_name'],
        'email' => $data['email'],
        'phone' => $data['phone'] ?? null,
        'status' => 'A',
        'password' => null, // Explicitly set to NULL - user must set password via token
    ]);
}
```

---

### 9.3 Actualizar Servicio: PasswordResetService

**Archivo:** `app/Services/PasswordResetService.php`

**Nuevo Método (si no existe):**

#### `createTokenForUser(User $user): string`
Genera token de reset para un usuario específico (sin enviar email).

```php
public function createTokenForUser(User $user): string
{
    // Eliminar tokens existentes
    Password::broker()->deleteToken($user);
    
    // Crear nuevo token
    $token = Password::broker()->createToken($user);
    
    return $token;
}
```

---

### 9.4 Actualizar Servicio: AuthService (SEGURIDAD CRÍTICA)

**Archivo:** `app/Services/AuthService.php`

**Modificación Requerida en método `login()`:**

#### PROBLEMA ACTUAL:
```php
if (! $user || ! Hash::check($credentials['password'], $user->password)) {
    throw ValidationException::withMessages(['email' => [__('auth.failed')]]);
}
```

**Vulnerabilidad:**
- Si `$user->password` es NULL, `Hash::check()` puede tener comportamiento inesperado
- Posibles ataques de timing para detectar usuarios sin contraseña
- No es explícitamente seguro

#### SOLUCIÓN (NUEVA IMPLEMENTACIÓN):
```php
/**
 * Authenticate a user and generate a token.
 *
 * SECURITY: Explicitly validates NULL passwords to prevent authentication
 * of users who haven't set their password yet (e.g., new branch_leaders).
 *
 * @throws ValidationException
 */
public function login(array $credentials): array
{
    $user = User::where('email', $credentials['email'])->first();

    // SECURITY: Explicitly check for NULL password before Hash::check
    // This prevents users without password from authenticating and
    // avoids potential timing attacks
    if (! $user || is_null($user->password) || ! Hash::check($credentials['password'], $user->password)) {
        throw ValidationException::withMessages([
            'email' => [__('auth.failed')],
        ]);
    }
    
    $token = $user->createToken('api-token')->plainTextToken;

    return [
        'token' => $token,
        'user' => $user,
    ];
}
```

#### Justificación de Seguridad:
1. ✅ **Previene autenticación de usuarios sin contraseña** - Usuarios creados como branch_leaders sin password no pueden hacer login hasta establecer su contraseña
2. ✅ **Evita comportamiento inesperado de Hash::check()** - No se llama Hash::check con NULL
3. ✅ **Previene ataques de timing** - La validación es explícita y temprana
4. ✅ **Mismo mensaje de error** - No revela si el usuario existe o no tiene contraseña (buena práctica de seguridad)
5. ✅ **Orden de validaciones** - Primero user exists, luego password not null, luego hash check

#### Archivos Relacionados:
- `app/Services/AuthService.php` - Implementar cambio
- `tests/Unit/Services/AuthServiceTest.php` - Agregar tests de seguridad (NUEVO)

---

## 10. Validaciones y Reglas de Negocio

### 10.1 Reglas de Validación

#### StoreCommerceBranchUserRequest
```php
public function rules(): array
{
    return [
        'name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email|unique:users,email|max:255',
        'phone' => 'nullable|string|regex:/^[0-9]{10}$/',
        'commerce_branch_id' => 'required|exists:commerce_branches,id',
    ];
}

public function withValidator($validator)
{
    $validator->after(function ($validator) {
        // Validar que la sucursal pertenezca al comercio
        $commerceId = $this->route('commerce_id');
        $branch = CommerceBranch::find($this->commerce_branch_id);
        
        if ($branch && $branch->commerce_id != $commerceId) {
            $validator->errors()->add(
                'commerce_branch_id',
                'La sucursal no pertenece al comercio especificado.'
            );
        }
    });
}
```

#### AssignUserToBranchRequest
```php
public function rules(): array
{
    return [
        'user_id' => [
            'required',
            'exists:users,id',
            function ($attribute, $value, $fail) {
                $user = User::find($value);
                
                // Validar rol branch_leader
                if (!$user->hasRole('branch_leader')) {
                    $fail('El usuario debe tener el rol branch_leader.');
                }
                
                // Validar que pertenezca al commerce
                $branchId = $this->route('branch_id');
                $branch = CommerceBranch::find($branchId);
                $commerceId = $branch->commerce_id;
                
                $belongsToCommerce = CommerceBranchUser::where('user_id', $value)
                    ->where('commerce_id', $commerceId)
                    ->exists();
                
                if (!$belongsToCommerce) {
                    $fail('El usuario no pertenece al comercio.');
                }
                
                // Validar que no esté ya asignado
                $alreadyAssigned = CommerceBranchUser::where('user_id', $value)
                    ->where('commerce_branch_id', $branchId)
                    ->exists();
                
                if ($alreadyAssigned) {
                    $fail('El usuario ya está asignado a esta sucursal.');
                }
            }
        ],
    ];
}
```

#### Actualizar StoreCommerceBranchRequest
```php
// Agregar a reglas existentes
'user_id' => [
    'nullable',
    'exists:users,id',
    function ($attribute, $value, $fail) {
        if ($value) {
            $user = User::find($value);
            
            // Validar rol branch_leader
            if (!$user->hasRole('branch_leader')) {
                $fail('El usuario debe tener el rol branch_leader.');
            }
            
            // Validar que pertenezca al commerce
            $commerceId = $this->commerce_id;
            $belongsToCommerce = CommerceBranchUser::where('user_id', $value)
                ->where('commerce_id', $commerceId)
                ->exists();
            
            if (!$belongsToCommerce) {
                $fail('El usuario no pertenece al comercio.');
            }
        }
    }
],
```

---

### 10.2 Reglas de Negocio

#### RN-01: Unicidad de Asignación
Un usuario solo puede estar asignado UNA VEZ a la misma sucursal (constraint de BD + validación).

#### RN-02: Múltiples Sucursales
Un usuario puede ser branch_leader de múltiples sucursales del mismo commerce.

#### RN-03: Múltiples Comercios
Un usuario puede ser branch_leader de sucursales de diferentes commerces (no hay restricción).

#### RN-04: Owner Permissions
Solo el owner del commerce (owner_user_id) o admins pueden:
- Listar usuarios del commerce
- Crear nuevos branch_leaders
- Asignar/desasignar usuarios a sucursales

#### RN-05: Email Único
El email debe ser único en toda la tabla users.

#### RN-06: Token Expiration
Token de newPassword expira en 60 minutos (configuración estándar de Laravel).

#### RN-07: Rol No Exclusivo
branch_leader puede coexistir con otros roles (ej: provider + branch_leader).

#### RN-08: Usuario Sin Contraseña
Usuarios creados vía endpoint de branch_leader se crean SIN contraseña inicial.

#### RN-09: Soft Deletes
Desasignaciones usan soft delete para mantener historial.

#### RN-10: Auditoría Obligatoria
Todas las operaciones de creación/asignación/desasignación deben registrarse en audit_logs.

---

## 11. Tests Unitarios y de Integración

### 11.1 Feature Tests

#### `tests/Feature/Api/V1/CommerceBranchUserTest.php`

**Test Cases:**
```php
// Autenticación y Autorización
test_guest_cannot_access_commerce_users_endpoints
test_user_without_permission_cannot_access_commerce_users
test_owner_can_access_their_commerce_users
test_admin_can_access_any_commerce_users
test_owner_cannot_access_other_commerce_users

// Listar Usuarios
test_can_list_commerce_users
test_list_shows_only_branch_leaders
test_list_includes_assigned_branches
test_list_returns_empty_when_no_users

// Crear Usuario
test_can_create_branch_leader_user
test_created_user_has_branch_leader_role
test_created_user_is_assigned_to_branch
test_created_user_receives_welcome_email
test_created_user_has_password_reset_token
test_cannot_create_user_with_duplicate_email
test_cannot_create_user_without_required_fields
test_cannot_create_user_with_invalid_email
test_cannot_create_user_with_invalid_phone
test_cannot_create_user_for_branch_of_different_commerce
test_phone_is_optional_when_creating_user

// Asignar Usuario
test_can_assign_existing_user_to_branch
test_assigned_user_receives_assignment_email
test_cannot_assign_user_without_branch_leader_role
test_cannot_assign_user_not_in_commerce
test_cannot_assign_user_already_assigned_to_branch
test_can_assign_user_to_multiple_branches

// Desasignar Usuario
test_can_remove_user_from_branch
test_removed_assignment_is_soft_deleted
test_cannot_remove_unassigned_user
test_removing_user_creates_audit_log

// Listar Usuarios de Sucursal
test_can_list_branch_users
test_branch_users_list_is_filtered_correctly

// Modificar Crear Sucursal
test_can_create_branch_with_user_assignment
test_branch_creation_with_invalid_user_fails
test_branch_creation_without_user_works

// Auditoría
test_creating_user_creates_audit_log
test_assigning_user_creates_audit_log

// Notificaciones
test_welcome_notification_is_queued
test_assignment_notification_is_queued
test_notifications_contain_correct_data
test_welcome_email_includes_token_and_branch_name
test_assignment_email_includes_branch_name
```

**Estructura del Test:**
```php
<?php

namespace Tests\Feature\Api\V1;

use Tests\TestCase;
use App\Models\User;
use App\Models\Commerce;
use App\Models\CommerceBranch;
use App\Models\CommerceBranchUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use App\Notifications\BranchLeaderWelcomeNotification;
use App\Notifications\BranchAssignmentNotification;

class CommerceBranchUserTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected Commerce $commerce;
    protected CommerceBranch $branch;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear owner con commerce y sucursal
        $this->owner = User::factory()->create();
        $this->owner->assignRole('provider');
        
        $this->commerce = Commerce::factory()->create([
            'owner_user_id' => $this->owner->id
        ]);
        
        $this->branch = CommerceBranch::factory()->create([
            'commerce_id' => $this->commerce->id
        ]);
    }

    // Tests...
}
```

---

### 11.2 Unit Tests

#### `tests/Unit/Services/CommerceBranchUserServiceTest.php`

**Test Cases:**
```php
test_getCommerceUsers_returns_only_branch_leaders
test_createAndAssign_creates_user_with_correct_data
test_createAndAssign_assigns_branch_leader_role
test_createAndAssign_creates_password_reset_token
test_createAndAssign_creates_commerce_branch_user_record
test_createAndAssign_sends_welcome_notification
test_assignUserToBranch_validates_user_has_role
test_assignUserToBranch_validates_user_belongs_to_commerce
test_assignUserToBranch_prevents_duplicate_assignment
test_assignUserToBranch_sends_assignment_notification
test_removeUserFromBranch_soft_deletes_record
test_getBranchUsers_returns_correct_users
test_validateUserBelongsToCommerce_returns_true_when_valid
test_validateUserBelongsToCommerce_returns_false_when_invalid
```

---

#### `tests/Unit/Notifications/BranchLeaderWelcomeNotificationTest.php`

**Test Cases:**
```php
test_notification_is_sent_via_mail
test_notification_uses_emails_queue
test_notification_has_correct_subject
test_notification_includes_user_name
test_notification_includes_branch_name
test_notification_includes_token
test_notification_includes_frontend_url_with_newPassword
test_notification_to_mail_returns_mailable
```

---

#### `tests/Unit/Notifications/BranchAssignmentNotificationTest.php`

**Test Cases:**
```php
test_notification_is_sent_via_mail
test_notification_uses_emails_queue
test_notification_has_correct_subject
test_notification_includes_user_name
test_notification_includes_branch_name
test_notification_includes_commerce_name
test_notification_does_not_include_token
test_notification_to_mail_returns_mailable
```

---

### 11.3 Model Tests

#### `tests/Unit/Models/CommerceBranchUserTest.php`

**Test Cases:**
```php
test_belongs_to_commerce
test_belongs_to_commerce_branch
test_belongs_to_user
test_fillable_attributes_are_mass_assignable
test_uses_soft_deletes
test_casts_dates_correctly
```

---

### 11.4 Request Validation Tests

#### `tests/Unit/Requests/StoreCommerceBranchUserRequestTest.php`

**Test Cases:**
```php
test_name_is_required
test_last_name_is_required
test_email_is_required_and_must_be_valid
test_email_must_be_unique
test_phone_is_optional
test_phone_must_match_regex
test_commerce_branch_id_is_required
test_commerce_branch_id_must_exist
test_branch_must_belong_to_commerce
```

---

### 11.5 Integration Tests

#### `tests/Feature/Api/V1/PasswordResetFlowForBranchLeaderTest.php`

**Test Cases:**
```php
test_new_branch_leader_receives_token
test_branch_leader_can_access_newPassword_with_valid_token
test_branch_leader_can_reset_password_using_token
test_branch_leader_can_login_after_setting_password
test_token_expires_after_60_minutes
test_token_cannot_be_reused
```

---

## 12. Documentación

### 12.1 Actualizar Swagger/OpenAPI

**Archivo:** `app/Http/Controllers/CommerceBranchUserController.php`

**Agregar Anotaciones:**
```php
/**
 * @OA\Get(
 *     path="/api/v1/commerces/{commerce_id}/users",
 *     tags={"Commerce Branch Users"},
 *     summary="Listar usuarios del comercio",
 *     description="Obtiene todos los usuarios con rol branch_leader asignados al comercio",
 *     operationId="getCommerceUsers",
 *     security={{"sanctum":{}}},
 *     @OA\Parameter(
 *         name="commerce_id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Lista de usuarios obtenida exitosamente",
 *         @OA\JsonContent(
 *             @OA\Property(property="status", type="string", example="success"),
 *             @OA\Property(property="data", type="array", @OA\Items(ref="#/components/schemas/CommerceBranchUserResource")),
 *             @OA\Property(property="message", type="string")
 *         )
 *     ),
 *     @OA\Response(response=401, description="No autenticado"),
 *     @OA\Response(response=403, description="No autorizado"),
 *     @OA\Response(response=404, description="Comercio no encontrado")
 * )
 */
public function index(int $commerceId) { }
```

**Documentar todos los endpoints nuevos con anotaciones OpenAPI completas.**

---

### 12.2 README de Funcionalidad

**Archivo:** `docs/commerce-branch-users.md` (NUEVO)

**Contenido:**
- Descripción general de la funcionalidad
- Diagrama de flujo
- Casos de uso
- Ejemplos de requests/responses
- Reglas de negocio
- FAQs

---

### 12.3 Actualizar Postman Collection

**Archivo:** `postman/Napa-API.postman_collection.json`

**Agregar:**
- Carpeta "Commerce Branch Users"
- Requests para todos los endpoints nuevos
- Variables de entorno necesarias
- Tests automatizados en Postman

---

### 12.4 Actualizar CHANGELOG

**Archivo:** `CHANGELOG.md`

**Agregar:**
```markdown
## [Unreleased]

### Added
- Nuevo rol `branch_leader` con permisos específicos para gestión de sucursales
- Tabla `commerce_branch_users` para relación M:N entre usuarios y sucursales
- Endpoint GET `/api/v1/commerces/{commerce_id}/users` para listar usuarios del comercio
- Endpoint POST `/api/v1/commerces/{commerce_id}/users` para crear nuevos branch leaders
- Endpoint POST `/api/v1/commerce-branches/{branch_id}/users` para asignar usuarios a sucursales
- Endpoint DELETE `/api/v1/commerce-branches/{branch_id}/users/{user_id}` para desasignar usuarios
- Endpoint GET `/api/v1/commerce-branches/{branch_id}/users` para listar usuarios de una sucursal
- Notificación `BranchLeaderWelcomeNotification` para nuevos usuarios con token de contraseña
- Notificación `BranchAssignmentNotification` para usuarios existentes asignados a sucursales
- Servicio `CommerceBranchUserService` para gestión de usuarios de sucursales
- Tests completos para funcionalidad de branch users

### Changed
- Endpoint POST `/api/v1/commerce-branches` ahora acepta campo opcional `user_id` para asignar usuario al crear sucursal
- Modelo `Commerce` incluye nuevas relaciones `commerceBranchUsers()` y `branchLeaders()`
- Modelo `CommerceBranch` incluye nuevas relaciones `commerceBranchUsers()` y `branchLeaders()`
- Modelo `User` incluye nuevas relaciones y método `isBranchLeader()`

### Security
- Validación de permisos para gestión de usuarios de sucursales
- Solo owners de commerces pueden gestionar branch leaders de sus comercios
- Auditoría completa de operaciones de asignación/desasignación
```

---

## 13. Plan de Ejecución

### Fase 1: Base de Datos y Modelos (Día 1)

**Tareas:**
1. ✅ Crear migración `2026_05_11_000000_create_commerce_branch_users_table.php`
2. ✅ Ejecutar migración y verificar estructura
3. ✅ Crear modelo `CommerceBranchUser.php` con relaciones
4. ✅ Actualizar modelo `Commerce.php` - agregar relaciones
5. ✅ Actualizar modelo `CommerceBranch.php` - agregar relaciones
6. ✅ Actualizar modelo `User.php` - agregar relaciones y método `isBranchLeader()`
7. ✅ Crear factory `CommerceBranchUserFactory.php` para tests
8. ✅ Tests de modelo: `CommerceBranchUserTest.php`

**Criterio de Aceptación:**
- Migración ejecuta sin errores
- Relaciones funcionan correctamente (verificar con Tinker)
- Tests de modelo pasan

---

### Fase 2: Roles y Permisos (Día 1)

**Tareas:**
1. ✅ Actualizar `RolePermissionSeeder.php` - agregar rol `branch_leader`
2. ✅ Verificar permisos existentes (`provider.products.show`, `provider.branches.update`)
3. ✅ Ejecutar seeder: `php artisan db:seed --class=RolePermissionSeeder`
4. ✅ Verificar en BD que rol y permisos se crearon correctamente
5. ✅ Test de seeder (opcional)

**Criterio de Aceptación:**
- Rol `branch_leader` existe en tabla `roles`
- Permisos correctos asignados en tabla `role_has_permissions`
- Usuario puede recibir rol branch_leader

---

### Fase 3: Servicios y Lógica de Negocio (Días 2-3)

**Tareas:**
1. ✅ Crear `CommerceBranchUserService.php`
   - Método `getCommerceUsers()`
   - Método `createAndAssign()`
   - Método `assignUserToBranch()`
   - Método `removeUserFromBranch()`
   - Método `getBranchUsers()`
   - Método `validateUserBelongsToCommerce()`
2. ✅ Actualizar `UserService.php` - agregar `createUserWithoutPassword()`
3. ✅ Actualizar `PasswordResetService.php` - agregar `createTokenForUser()`
4. ✅ Tests unitarios: `CommerceBranchUserServiceTest.php`
5. ✅ Inyectar servicios en Service Provider si es necesario

**Criterio de Aceptación:**
- Todos los métodos funcionan correctamente
- Tests unitarios de servicio pasan
- Manejo de errores implementado

---

### Fase 4: Notificaciones (Día 3)

**Tareas:**
1. ✅ Crear `BranchLeaderWelcomeNotification.php`
   - Implementar `toMail()`
   - Incluir token, branch name, frontend URL
   - Configurar queue 'emails'
2. ✅ Crear `BranchAssignmentNotification.php`
   - Implementar `toMail()`
   - Incluir branch name, commerce name
   - Sin token
3. ✅ Tests de notificaciones
   - `BranchLeaderWelcomeNotificationTest.php`
   - `BranchAssignmentNotificationTest.php`
4. ✅ Verificar envío con Mailtrap o similar

**Criterio de Aceptación:**
- Notificaciones se encolan correctamente
- Contenido de emails es correcto
- Tests de notificaciones pasan

---

### Fase 5: Requests y Validaciones (Día 4)

**Tareas:**
1. ✅ Crear `GetCommerceBranchUsersRequest.php`
2. ✅ Crear `StoreCommerceBranchUserRequest.php`
3. ✅ Crear `AssignUserToBranchRequest.php`
4. ✅ Actualizar `StoreCommerceBranchRequest.php` - agregar validación `user_id`
5. ✅ Tests de validación para cada Request
6. ✅ Verificar mensajes de error personalizados

**Criterio de Aceptación:**
- Validaciones funcionan correctamente
- Reglas de negocio se cumplen
- Tests de validación pasan

---

### Fase 6: Controllers y Endpoints (Días 5-6)

**Tareas:**
1. ✅ Crear `CommerceBranchUserController.php`
   - `index()` - GET /commerces/{id}/users
   - `store()` - POST /commerces/{id}/users
   - `assignToBranch()` - POST /commerce-branches/{id}/users
   - `removeFromBranch()` - DELETE /commerce-branches/{id}/users/{user_id}
   - `getBranchUsers()` - GET /commerce-branches/{id}/users
2. ✅ Crear `CommerceBranchUserResource.php`
3. ✅ Actualizar `CommerceBranchController.php` - modificar método `store()`
4. ✅ Agregar rutas en `routes/api.php`
5. ✅ Agregar middleware y permisos
6. ✅ Tests de feature: `CommerceBranchUserTest.php`

**Criterio de Aceptación:**
- Todos los endpoints responden correctamente
- Autorización funciona (permisos)
- Tests de feature pasan
- Códigos de estado HTTP correctos

---

### Fase 7: Integración con Password Reset (Día 6)

**Tareas:**
1. ✅ Verificar que endpoint `POST /api/v1/password/reset` funcione con tokens generados
2. ✅ Test de integración: `PasswordResetFlowForBranchLeaderTest.php`
3. ✅ Verificar flujo completo:
   - Crear usuario → Recibir email → Usar token → Establecer contraseña → Login
4. ✅ Verificar redirección frontend a `/newPassword`

**Criterio de Aceptación:**
- Usuario nuevo puede establecer contraseña con token
- Token expira en 60 minutos
- Flujo completo funciona end-to-end

---

### Fase 8: Auditoría (Día 7)

**Tareas:**
1. ✅ Implementar llamadas a `AuditLogService` en:
   - `CommerceBranchUserService->createAndAssign()`
   - `CommerceBranchUserService->assignUserToBranch()`
   - `CommerceBranchUserService->removeUserFromBranch()`
2. ✅ Definir eventos de auditoría:
   - `branch_leader.created`
   - `branch_leader.assigned`
   - `branch_leader.removed`
3. ✅ Tests de auditoría
4. ✅ Verificar logs en BD

**Criterio de Aceptación:**
- Todas las operaciones se registran en `audit_logs`
- Logs incluyen user_id, action, metadata
- Tests de auditoría pasan

---

### Fase 9: Documentación API (Día 7)

**Tareas:**
1. ✅ Agregar anotaciones OpenAPI a `CommerceBranchUserController`
2. ✅ Agregar schemas de request/response
3. ✅ Generar documentación Swagger: `php artisan l5-swagger:generate`
4. ✅ Verificar en `/api/documentation`
5. ✅ Actualizar Postman collection
6. ✅ Crear ejemplos de requests

**Criterio de Aceptación:**
- Swagger muestra todos los nuevos endpoints
- Documentación es clara y completa
- Postman collection funciona

---

### Fase 10: Documentación General (Día 8)

**Tareas:**
1. ✅ Crear `docs/commerce-branch-users.md`
2. ✅ Actualizar `CHANGELOG.md`
3. ✅ Actualizar `README.md` si es necesario
4. ✅ Crear diagramas de flujo (Mermaid o similar)
5. ✅ Documentar reglas de negocio
6. ✅ Crear guía de troubleshooting

**Criterio de Aceptación:**
- Documentación completa y actualizada
- Ejemplos claros
- FAQs documentadas

---

### Fase 11: Testing Completo (Día 9)

**Tareas:**
1. ✅ Ejecutar suite completa de tests: `php artisan test`
2. ✅ Verificar cobertura de código (si está configurado)
3. ✅ Tests de integración completos
4. ✅ Tests de edge cases
5. ✅ Tests de seguridad y permisos
6. ✅ Corregir tests fallidos

**Criterio de Aceptación:**
- 100% de tests pasan
- Cobertura > 80% en código nuevo
- Sin errores ni warnings

---

### Fase 12: QA y Refinamiento (Día 10)

**Tareas:**
1. ✅ Testing manual de flujo completo
2. ✅ Verificar emails en staging
3. ✅ Verificar performance (queries N+1)
4. ✅ Code review interno
5. ✅ Refactorizar si es necesario
6. ✅ Optimizar queries
7. ✅ Verificar manejo de errores

**Criterio de Aceptación:**
- Flujo completo funciona sin errores
- No hay queries N+1
- Código cumple estándares (Laravel Pint)
- Manejo de errores robusto

---

### Fase 13: Deployment (Día 11)

**Tareas:**
1. ✅ Crear PR con todos los cambios
2. ✅ Code review por equipo
3. ✅ Merge a develop/staging
4. ✅ Ejecutar migraciones en staging: `php artisan migrate`
5. ✅ Ejecutar seeders: `php artisan db:seed --class=RolePermissionSeeder`
6. ✅ Smoke tests en staging
7. ✅ Deployment a producción
8. ✅ Monitoreo post-deployment

**Criterio de Aceptación:**
- PR aprobado y mergeado
- Migraciones ejecutadas sin errores
- Funcionalidad working en producción
- Sin errores en logs

---

## Resumen de Archivos a Crear

### Migraciones (1)
- `database/migrations/2026_05_11_000000_create_commerce_branch_users_table.php`

### Modelos (1)
- `app/Models/CommerceBranchUser.php`

### Servicios (1)
- `app/Services/CommerceBranchUserService.php`

### Controllers (1)
- `app/Http/Controllers/CommerceBranchUserController.php`

### Requests (3)
- `app/Http/Requests/GetCommerceBranchUsersRequest.php`
- `app/Http/Requests/StoreCommerceBranchUserRequest.php`
- `app/Http/Requests/AssignUserToBranchRequest.php`

### Resources (1)
- `app/Http/Resources/CommerceBranchUserResource.php`

### Notificaciones (2)
- `app/Notifications/BranchLeaderWelcomeNotification.php`
- `app/Notifications/BranchAssignmentNotification.php`

### Factories (1)
- `database/factories/CommerceBranchUserFactory.php`

### Tests Feature (2)
- `tests/Feature/Api/V1/CommerceBranchUserTest.php`
- `tests/Feature/Api/V1/PasswordResetFlowForBranchLeaderTest.php`

### Tests Unit (5)
- `tests/Unit/Models/CommerceBranchUserTest.php`
- `tests/Unit/Services/CommerceBranchUserServiceTest.php`
- `tests/Unit/Notifications/BranchLeaderWelcomeNotificationTest.php`
- `tests/Unit/Notifications/BranchAssignmentNotificationTest.php`
- `tests/Unit/Requests/StoreCommerceBranchUserRequestTest.php`

### Documentación (1)
- `docs/commerce-branch-users.md`

**Total: 20 archivos nuevos**

---

## Resumen de Archivos a Modificar

### Seeders (1)
- `database/seeders/RolePermissionSeeder.php`

### Modelos (3)
- `app/Models/Commerce.php`
- `app/Models/CommerceBranch.php`
- `app/Models/User.php`

### Servicios (2)
- `app/Services/UserService.php`
- `app/Services/PasswordResetService.php`

### Requests (1)
- `app/Http/Requests/StoreCommerceBranchRequest.php`

### Controllers (1)
- `app/Http/Controllers/CommerceBranchController.php`

### Rutas (1)
- `routes/api.php`

### Documentación (2)
- `CHANGELOG.md`
- `README.md` (si necesario)

**Total: 11 archivos a modificar**

---

## Estimación de Tiempo

| Fase | Días | Complejidad |
|------|------|-------------|
| Fase 1: Base de Datos y Modelos | 1 | Media |
| Fase 2: Roles y Permisos | 0.5 | Baja |
| Fase 3: Servicios y Lógica | 2 | Alta |
| Fase 4: Notificaciones | 1 | Media |
| Fase 5: Requests y Validaciones | 1 | Media |
| Fase 6: Controllers y Endpoints | 2 | Alta |
| Fase 7: Integración Password Reset | 0.5 | Media |
| Fase 8: Auditoría | 0.5 | Baja |
| Fase 9: Documentación API | 0.5 | Baja |
| Fase 10: Documentación General | 1 | Media |
| Fase 11: Testing Completo | 1 | Media |
| Fase 12: QA y Refinamiento | 1 | Media |
| Fase 13: Deployment | 0.5 | Baja |
| **TOTAL** | **13 días** | **Media-Alta** |

**Nota:** Estimación para 1 desarrollador full-time. Con 2 desarrolladores trabajando en paralelo, se puede reducir a 7-8 días.

---

## Riesgos y Mitigaciones

### Riesgo 1: Usuarios sin contraseña no pueden resetear
**Probabilidad:** Media  
**Impacto:** Alto  
**Mitigación:** 
- Usar tabla password_reset_tokens que ya maneja este caso
- Tests exhaustivos del flujo completo
- Validar que token funciona antes de enviar email

### Riesgo 2: Emails no se envían correctamente
**Probabilidad:** Media  
**Impacto:** Alto  
**Mitigación:**
- Implementar graceful failure (como en UserService)
- Logs detallados de envío de emails
- Monitoring de queue 'emails'
- Tests de notificaciones con fake dispatcher

### Riesgo 3: Queries N+1 al listar usuarios con sucursales
**Probabilidad:** Alta  
**Impacto:** Medio  
**Mitigación:**
- Usar eager loading: `with(['assignedBranches', 'roles'])`
- Tests de performance
- Usar Laravel Debugbar en desarrollo

### Riesgo 4: Conflictos de permisos entre roles
**Probabilidad:** Baja  
**Impacto:** Medio  
**Mitigación:**
- Documentar claramente permisos de cada rol
- Tests de autorización exhaustivos
- Code review de lógica de permisos

### Riesgo 5: Migración falla en producción
**Probabilidad:** Baja  
**Impacto:** Crítico  
**Mitigación:**
- Probar migración en staging primero
- Backup de BD antes de migración
- Rollback plan preparado
- Migración es aditiva (no destructiva)

---

## Checklist de Calidad

### Código
- [ ] Sigue PSR-12 y estándares de Laravel
- [ ] Pasa Laravel Pint sin errores
- [ ] Sin código duplicado
- [ ] Nombres descriptivos y consistentes
- [ ] Comentarios en código complejo
- [ ] Type hints en todos los métodos

### Tests
- [ ] Cobertura > 80% en código nuevo
- [ ] Tests de edge cases
- [ ] Tests de seguridad y permisos
- [ ] Tests de integración completos
- [ ] Tests pasan en CI/CD

### Seguridad
- [ ] Validación de inputs
- [ ] Autorización en todos los endpoints
- [ ] Sanitización de datos
- [ ] Protección contra CSRF (Sanctum)
- [ ] Rate limiting configurado
- [ ] Auditoría de operaciones sensibles

### Performance
- [ ] Sin queries N+1
- [ ] Índices en tablas creadas
- [ ] Eager loading usado correctamente
- [ ] Paginación en listados grandes
- [ ] Cache considerado si es necesario

### Documentación
- [ ] README actualizado
- [ ] CHANGELOG actualizado
- [ ] Swagger/OpenAPI completo
- [ ] Postman collection actualizado
- [ ] Diagramas de flujo creados
- [ ] FAQs documentadas

### Deployment
- [ ] Migraciones probadas en staging
- [ ] Seeders ejecutados correctamente
- [ ] Rollback plan documentado
- [ ] Monitoring configurado
- [ ] Logs revisados post-deployment

---

## Conclusión

Este plan proporciona una guía completa y detallada para implementar el sistema de usuarios de sucursales (Commerce Branch Users) con el rol `branch_leader`. La implementación está dividida en 13 fases lógicas y secuenciales, con criterios de aceptación claros para cada una.

El plan cubre:
- ✅ Arquitectura de base de datos con relaciones M:N
- ✅ Sistema completo de roles y permisos
- ✅ 5 nuevos endpoints RESTful
- ✅ 2 notificaciones por email (welcome + assignment)
- ✅ Integración con sistema de password reset
- ✅ Auditoría completa de operaciones
- ✅ Tests exhaustivos (feature + unit + integration)
- ✅ Documentación completa (API + general)

**Próximos Pasos:**
1. Revisar y aprobar este plan
2. Resolver dudas o ajustes necesarios
3. Comenzar implementación por Fase 1

**Pregunta:** ¿Deseas que proceda con la implementación siguiendo este plan, o hay algún ajuste que te gustaría hacer primero?

---

**Fin del Plan**
