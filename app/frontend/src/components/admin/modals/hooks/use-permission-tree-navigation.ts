import { useMemo, useState } from 'react';
import { PermissionTree } from '../../../../types/role-form-types';
import { getPermissionsFromPath } from '../../../../utils/permission-tree-builder';

export interface PermissionTreeContentItem {
  key: string;
  name: string;
  type: 'module' | 'sidebar' | 'entity' | 'permission';
  action?: string;
}

interface UsePermissionTreeNavigationParams {
  permissionTree: PermissionTree;
}

export function usePermissionTreeNavigation({ permissionTree }: UsePermissionTreeNavigationParams) {
  const [currentPath, setCurrentPath] = useState<string[]>([]);

  const navigateTo = (level: string) => {
    setCurrentPath(prev => [...prev, level]);
  };

  const navigateBack = () => {
    setCurrentPath(prev => prev.slice(0, -1));
  };

  const navigateHome = () => {
    setCurrentPath([]);
  };

  const navigateToBreadcrumbIndex = (index: number) => {
    if (index === 0) {
      navigateHome();
      return;
    }

    setCurrentPath(prev => prev.slice(0, index));
  };

  const currentContent = useMemo<PermissionTreeContentItem[]>(() => {
    if (currentPath.length === 0) {
      return Object.keys(permissionTree).map(moduleKey => ({
        key: moduleKey,
        name: permissionTree[moduleKey].name,
        type: 'module' as const
      }));
    }

    if (currentPath.length === 1) {
      const moduleKey = currentPath[0];
      const treeModule = permissionTree[moduleKey];

      return Object.keys(treeModule?.children || {}).map(sidebarKey => ({
        key: sidebarKey,
        name: treeModule.children![sidebarKey].name,
        type: 'sidebar' as const
      }));
    }

    if (currentPath.length === 2) {
      const [moduleKey, sidebarKey] = currentPath;
      const sidebar = permissionTree[moduleKey]?.children?.[sidebarKey];

      return Object.keys(sidebar?.children || {}).map(entityKey => ({
        key: entityKey,
        name: sidebar!.children![entityKey].name,
        type: 'entity' as const
      }));
    }

    if (currentPath.length === 3) {
      const permissions = getPermissionsFromPath(permissionTree, currentPath);

      return permissions.map(permission => ({
        key: permission.name,
        name: permission.description,
        type: 'permission' as const,
        action: permission.action
      }));
    }

    return [];
  }, [permissionTree, currentPath]);

  const breadcrumb = useMemo(() => {
    const nextBreadcrumb = ['Permisos'];

    if (currentPath.length > 0) {
      nextBreadcrumb.push(permissionTree[currentPath[0]]?.name || '');
    }

    if (currentPath.length > 1) {
      const moduleKey = currentPath[0];
      const sidebarKey = currentPath[1];
      nextBreadcrumb.push(permissionTree[moduleKey]?.children?.[sidebarKey]?.name || '');
    }

    if (currentPath.length > 2) {
      const [moduleKey, sidebarKey, entityKey] = currentPath;
      nextBreadcrumb.push(permissionTree[moduleKey]?.children?.[sidebarKey]?.children?.[entityKey]?.name || '');
    }

    return nextBreadcrumb;
  }, [permissionTree, currentPath]);

  return {
    currentPath,
    currentContent,
    breadcrumb,
    canGoBack: currentPath.length > 0,
    isAtPermissionLevel: currentPath.length === 3,
    navigateTo,
    navigateBack,
    navigateHome,
    navigateToBreadcrumbIndex
  };
}
