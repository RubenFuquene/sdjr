# 📋 **INSTRUCCIONES PARA TESTER BACKEND**
## Permission System Refactoring - Test Adjustments Required

### 🎯 **Contexto del Cambio**
Se ha refactorizado el sistema de permisos para implementar una estructura jerárquica organizada por módulos:
- **Admin modules**: `admin.*` (profiles, parametrization, marketing, etc.)
- **Provider modules**: `provider.*` (basic_data, commerces, products, etc.)
- **Legacy permissions**: Mantienen estructura original (countries.*, departments.*, etc.)

### ✅ **Tests que NO requieren cambios**
Los siguientes tests ya están funcionando correctamente con la nueva estructura:
- `CommerceTest` ✅
- `EstablishmentTypeTest` ✅
- `LegalRepresentativeFeatureTest` ✅
- Todos los tests de entidades básicas (Country, Department, City, etc.) ✅

### 🔧 **Tests que PUEDEN requerir ajustes**

#### 1. **Tests de Módulos Admin** (Posible impacto)
Si existen tests que verifican permisos específicos de módulos admin, actualizar:
```php
// ❌ Antes
$user->givePermissionTo('admin_profiles.view');

// ✅ Después
$user->givePermissionTo('admin.profiles.view');
```

#### 2. **Tests de Módulos Provider** (Posible impacto)
Si existen tests que verifican permisos específicos de módulos provider, actualizar:
```php
// ❌ Antes
$user->givePermissionTo('provider_basic_data.view');

// ✅ Después
$user->givePermissionTo('provider.basic_data.view');
```

#### 3. **Tests de Wildcard Permissions** (Nueva funcionalidad)
Agregar tests para verificar que Spatie soporta wildcards:
```php
// Test para verificar permisos jerárquicos
$user->givePermissionTo('admin.*'); // Debería dar acceso a todos los admin.*
$this->assertTrue($user->hasPermissionTo('admin.profiles.view'));
$this->assertTrue($user->hasPermissionTo('admin.dashboard.view'));
```

### 🧪 **Plan de Testing Recomendado**

#### **Fase 1: Verificación Básica**
```bash
# Ejecutar todos los tests para verificar baseline
docker compose exec backend php artisan test

# Verificar específicamente módulos con permisos
docker compose exec backend php artisan test --filter="CommerceTest|EstablishmentTypeTest|LegalRepresentativeFeatureTest"
```

#### **Fase 2: Verificación de Permisos Jerárquicos**
Crear nuevo test `PermissionHierarchyTest.php`:
```php
<?php
class PermissionHierarchyTest extends TestCase
{
    public function test_admin_wildcard_permissions()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('admin.*');

        // Verificar que tiene acceso a todos los permisos admin
        $adminPermissions = ['admin.profiles.view', 'admin.dashboard.view', 'admin.parametrization.view'];
        foreach ($adminPermissions as $permission) {
            $this->assertTrue($user->hasPermissionTo($permission));
        }
    }

    public function test_provider_wildcard_permissions()
    {
        $user = User::factory()->create();
        $user->givePermissionTo('provider.*');

        // Verificar que tiene acceso a todos los permisos provider
        $providerPermissions = ['provider.basic_data.view', 'provider.dashboard.view', 'provider.commerces.view'];
        foreach ($providerPermissions as $permission) {
            $this->assertTrue($user->hasPermissionTo($permission));
        }
    }
}
```

#### **Fase 3: Verificación de Seguridad**
```php
public function test_user_cannot_access_admin_modules_without_permission()
{
    $user = User::factory()->create();
    $user->givePermissionTo('provider.*'); // Solo permisos provider

    // Verificar que NO tiene acceso a módulos admin
    $this->assertFalse($user->hasPermissionTo('admin.profiles.view'));
    $this->assertFalse($user->hasPermissionTo('admin.dashboard.view'));
}
```

### 📊 **Comandos Útiles para Debugging**

```bash
# Ver permisos actuales
docker compose exec backend php artisan tinker --execute="Spatie\Permission\Models\Permission::all()->pluck('name')"

# Ver permisos por módulo
docker compose exec backend php artisan tinker --execute="Spatie\Permission\Models\Permission::where('name', 'LIKE', 'admin.%')->pluck('name')"
docker compose exec backend php artisan tinker --execute="Spatie\Permission\Models\Permission::where('name', 'LIKE', 'provider.%')->pluck('name')"

# Limpiar y reseedear permisos (si es necesario)
docker compose exec backend php artisan migrate:fresh --seed
```

### 🎯 **Criterios de Aceptación**
- [ ] Todos los tests existentes pasan ✅
- [ ] Nuevos tests de jerarquía de permisos implementados
- [ ] Verificación de que wildcards funcionan correctamente
- [ ] Tests de seguridad (usuario no puede acceder a módulos no autorizados)
- [ ] Documentación actualizada con nueva estructura de permisos

### 🚨 **Notas Importantes**
1. **Compatibilidad**: Spatie Permission soporta completamente la estructura jerárquica con puntos
2. **Performance**: Los wildcards pueden afectar performance - considerar cache
3. **Migración**: Los permisos legacy se mantienen para compatibilidad
4. **Testing**: Usar `RefreshDatabase` en tests para aislamiento

---
**Fecha:** 2025-12-26
**Prioridad:** HIGH
**Estimación:** 2-4 horas