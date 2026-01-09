/**
 * 🔄 ADAPTADOR DE PERMISOS: Parser estructura 4 niveles
 * 
 * PROPÓSITO: Parsea permisos del backend (formato: module.sidebar.entity.action)
 *            en estructura tipada para uso en frontend
 * 
 * EJEMPLO: "admin.profiles.roles.edit" → { module: 'admin', sidebar: 'profiles', entity: 'roles', action: 'edit' }
 */

import { PermissionFromAPI, PermissionAdapted } from '../../../types/role-form-types';

/**
 * Parsea permisos de formato string a estructura tipada de 4 niveles
 * Backend ya devuelve permisos con estructura: module.sidebar.entity.action
 */
export function adaptPermissions(permissions: PermissionFromAPI[]): PermissionAdapted[] {
  return permissions.map(permission => {
    const [module, sidebar, entity, action] = permission.name.split('.');
    
    return {
      name: permission.name,
      description: permission.description,
      module,
      sidebar,
      entity,
      action
    };
  });
}

/**
 * Obtiene nombre display para grupos sidebar
 */
export function getSidebarDisplayName(sidebar: string): string {
  const sidebarNames: Record<string, string> = {
    'profiles': 'Perfiles',
    'parametrization': 'Parametrización', 
    'marketing': 'Marketing',
    'dashboard': 'Dashboard',
    'support': 'Soporte'
  };
  return sidebarNames[sidebar] || capitalize(sidebar);
}

/**
 * Obtiene nombre display para módulos
 */
export function getModuleDisplayName(module: string): string {
  const moduleNames: Record<string, string> = {
    'admin': 'Administrador',
    'provider': 'Proveedor',
    'customer': 'Cliente'
  };
  return moduleNames[module] || capitalize(module);
}

/**
 * Capitaliza string
 */
function capitalize(str: string): string {
  return str.charAt(0).toUpperCase() + str.slice(1);
}