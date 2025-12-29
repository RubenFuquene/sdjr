/**
 * 🔄 ADAPTADOR TEMPORAL: Conversión 3→4 niveles
 * 
 * PROPÓSITO: Permite que frontend use estructura 4 niveles
 *            mientras backend mantiene 3 niveles
 * 
 * MIGRACIÓN: Eliminar este archivo cuando backend implemente 4 niveles
 */

import { PermissionFromAPI, PermissionAdapted } from '../../../types/role-form-types';

/**
 * Adapta permisos de 3 niveles (backend) a 4 niveles (frontend)
 */
export function adaptPermissions(permissions: PermissionFromAPI[]): PermissionAdapted[] {
  return permissions.map(permission => {
    const [module, entity, action] = permission.name.split('.');
    
    // 🎯 MAPEO AUTOMÁTICO: entidad → grupo sidebar
    const sidebarGroup = getSidebarGroupFromEntity(entity);
    const adaptedName = `${module}.${sidebarGroup}.${entity}.${action}`;
    
    return {
      name: adaptedName,
      description: permission.description,
      module,
      sidebar: sidebarGroup,
      entity,
      action
    };
  });
}

/**
 * Convierte permisos adaptados (4 niveles) de vuelta a originales (3 niveles)
 * Para envío al backend
 */
export function reverseAdaptPermissions(adaptedNames: string[]): string[] {
  return adaptedNames.map(adaptedName => {
    const [module, , entity, action] = adaptedName.split('.');
    return `${module}.${entity}.${action}`;
  });
}

/**
 * 📍 MAPEO TEMPORAL: entidad → grupo sidebar
 * 
 * Se elimina automáticamente cuando backend implemente 4 niveles
 */
function getSidebarGroupFromEntity(entity: string): string {
  const entityToSidebarMap: Record<string, string> = {
    // Grupo Profiles
    'roles': 'profiles',
    'users': 'profiles', 
    'permissions': 'profiles',
    
    // Grupo Parametrization
    'countries': 'parametrization',
    'departments': 'parametrization',
    'cities': 'parametrization',
    'establishments': 'parametrization',
    
    // Grupo Marketing
    'campaigns': 'marketing',
    
    // Módulos directos
    'dashboard': 'dashboard',
    'support': 'support'
  };
  
  return entityToSidebarMap[entity] || entity;
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