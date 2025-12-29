/**
 * Constructor de árbol de permisos 4 niveles
 * Genera estructura jerárquica automáticamente desde permisos adaptados
 */

import { PermissionAdapted, PermissionTree, PermissionTreeNode } from '../types/role-form-types';
import { getModuleDisplayName, getSidebarDisplayName } from '../components/admin/adapters/permission-adapter';

/**
 * Construye árbol 4 niveles desde permisos adaptados
 */
export function buildPermissionTree(permissions: PermissionAdapted[]): PermissionTree {
  return permissions.reduce((tree, permission) => {
    const { module: permissionModule, sidebar, entity } = permission;
    
    // Nivel 1: Módulo
    if (!tree[permissionModule]) {
      tree[permissionModule] = { 
        name: getModuleDisplayName(permissionModule),
        level: 'module',
        children: {} 
      };
    }
    
    // Nivel 2: Sidebar Group (AUTO-MAPEO)
    if (!tree[permissionModule].children![sidebar]) {
      tree[permissionModule].children![sidebar] = {
        name: getSidebarDisplayName(sidebar),
        level: 'sidebar',
        children: {}
      };
    }
    
    // Nivel 3: Entidad
    if (!tree[permissionModule].children![sidebar].children![entity]) {
      tree[permissionModule].children![sidebar].children![entity] = {
        name: capitalizeEntity(entity),
        level: 'entity', 
        permissions: []
      };
    }
    
    // Nivel 4: Acciones (se agregan como permisos)
    tree[permissionModule].children![sidebar].children![entity].permissions!.push(permission);
    
    return tree;
  }, {} as PermissionTree);
}

/**
 * Obtiene todas las rutas posibles del árbol para navegación
 */
export function getTreePaths(tree: PermissionTree): string[][] {
  const paths: string[][] = [];
  
  Object.keys(tree).forEach(moduleKey => {
    const treeModule = tree[moduleKey];
    
    Object.keys(treeModule.children || {}).forEach(sidebarKey => {
      const sidebar = treeModule.children![sidebarKey];
      
      Object.keys(sidebar.children || {}).forEach(entityKey => {
        paths.push([moduleKey, sidebarKey, entityKey]);
      });
    });
  });
  
  return paths;
}

/**
 * Navega en el árbol siguiendo una ruta específica
 */
export function navigateTreePath(tree: PermissionTree, path: string[]): PermissionTreeNode | null {
  let current: PermissionTreeNode | null = null;
  
  for (let i = 0; i < path.length; i++) {
    const key = path[i];
    
    if (i === 0) {
      // Nivel módulo
      current = tree[key] || null;
    } else {
      // Niveles anidados
      if (current && current.children) {
        current = current.children[key] || null;
      } else {
        current = null;
      }
    }
    
    if (!current) break;
  }
  
  return current;
}

/**
 * Obtiene todos los permisos de una ruta específica
 */
export function getPermissionsFromPath(tree: PermissionTree, path: string[]): PermissionAdapted[] {
  const node = navigateTreePath(tree, path);
  return node?.permissions || [];
}

/**
 * Capitaliza nombre de entidad
 */
function capitalizeEntity(entity: string): string {
  const entityNames: Record<string, string> = {
    'roles': 'Roles',
    'users': 'Usuarios',
    'permissions': 'Permisos',
    'countries': 'Países',
    'departments': 'Departamentos', 
    'cities': 'Ciudades',
    'establishments': 'Establecimientos',
    'campaigns': 'Campañas',
    'dashboard': 'Dashboard',
    'basic_data': 'Datos Básicos'
  };
  
  return entityNames[entity] || entity.charAt(0).toUpperCase() + entity.slice(1);
}