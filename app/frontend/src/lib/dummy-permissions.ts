/**
 * 🚨 TEMPORAL: Utilidad dummy que simula GET /api/v1/permissions
 * 
 * PROPÓSITO: Permitir desarrollo frontend independiente del backend
 * REEMPLAZAR: Con fetch real cuando endpoint esté disponible
 */

import { PermissionFromAPI } from '../types/role-form-types';

/**
 * Datos estáticos que simulan la respuesta del seeder backend
 * Estructura 3 niveles como viene actualmente del backend
 */
const DUMMY_PERMISSIONS: PermissionFromAPI[] = [
  // Admin - Roles
  { name: 'admin.roles.create', description: 'Crear roles' },
  { name: 'admin.roles.view', description: 'Ver roles' },
  { name: 'admin.roles.update', description: 'Editar roles' },
  { name: 'admin.roles.delete', description: 'Eliminar roles' },
  
  // Admin - Users
  { name: 'admin.users.create', description: 'Crear usuarios' },
  { name: 'admin.users.view', description: 'Ver usuarios' },
  { name: 'admin.users.edit', description: 'Editar usuarios' },
  { name: 'admin.users.delete', description: 'Eliminar usuarios' },
  
  // Admin - Countries
  { name: 'admin.countries.create', description: 'Crear países' },
  { name: 'admin.countries.view', description: 'Ver países' },
  { name: 'admin.countries.edit', description: 'Editar países' },
  { name: 'admin.countries.delete', description: 'Eliminar países' },
  
  // Admin - Departments
  { name: 'admin.departments.create', description: 'Crear departamentos' },
  { name: 'admin.departments.view', description: 'Ver departamentos' },
  { name: 'admin.departments.edit', description: 'Editar departamentos' },
  { name: 'admin.departments.delete', description: 'Eliminar departamentos' },
  
  // Admin - Cities
  { name: 'admin.cities.create', description: 'Crear ciudades' },
  { name: 'admin.cities.view', description: 'Ver ciudades' },
  { name: 'admin.cities.edit', description: 'Editar ciudades' },
  { name: 'admin.cities.delete', description: 'Eliminar ciudades' },
  
  // Admin - Dashboard
  { name: 'admin.dashboard.view', description: 'Ver dashboard' },
  
  // Provider - Basic Data
  { name: 'provider.basic_data.view', description: 'Ver datos básicos' },
  { name: 'provider.basic_data.edit', description: 'Editar datos básicos' },
  
  // Provider - Establishments
  { name: 'provider.establishments.create', description: 'Crear establecimientos' },
  { name: 'provider.establishments.view', description: 'Ver establecimientos' },
  { name: 'provider.establishments.edit', description: 'Editar establecimientos' },
  
  // Provider - Campaigns
  { name: 'provider.campaigns.create', description: 'Crear campañas' },
  { name: 'provider.campaigns.view', description: 'Ver campañas' },
  { name: 'provider.campaigns.edit', description: 'Editar campañas' },
];

/**
 * 🚨 FUNCIÓN TEMPORAL: Simula fetch a /api/v1/permissions
 * 
 * TODO: Reemplazar con:
 * ```typescript
 * const response = await fetch('/api/v1/permissions');
 * return response.json();
 * ```
 */
export async function fetchDummyPermissions(): Promise<PermissionFromAPI[]> {
  // Simular delay de red
  await new Promise(resolve => setTimeout(resolve, 300));
  
  console.warn('🚨 USANDO DATOS DUMMY - Reemplazar con endpoint real');
  
  return DUMMY_PERMISSIONS;
}

/**
 * Función helper para filtrar permisos por módulo
 */
export function filterPermissionsByModule(permissions: PermissionFromAPI[], module: string): PermissionFromAPI[] {
  return permissions.filter(permission => permission.name.startsWith(`${module}.`));
}