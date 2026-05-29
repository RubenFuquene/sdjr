import { useMemo } from 'react';
import { PermissionTree } from '../../../../types/role-form-types';
import { getPermissionsFromPath } from '../../../../utils/permission-tree-builder';
import { PermissionTreeContentItem } from './use-permission-tree-navigation';

export interface SelectedPermissionDetail {
  key: string;
  description: string;
  action: string;
  moduleKey: string;
  moduleName: string;
  sidebarName: string;
  entityName: string;
}

interface UsePermissionSummaryParams {
  permissionTree: PermissionTree;
  selectedPermissions: string[];
  summarySearch: string;
  currentContent: PermissionTreeContentItem[];
}

export function usePermissionSummary({
  permissionTree,
  selectedPermissions,
  summarySearch,
  currentContent
}: UsePermissionSummaryParams) {
  const permissionSummary = useMemo(() => {
    const summary = {
      total: selectedPermissions.length,
      byModule: {} as Record<string, number>,
      byAction: {} as Record<string, number>
    };

    selectedPermissions.forEach(permissionName => {
      const parts = permissionName.split('.');
      if (parts.length >= 4) {
        const moduleKey = parts[0];
        const action = parts[parts.length - 1];

        summary.byModule[moduleKey] = (summary.byModule[moduleKey] || 0) + 1;
        summary.byAction[action] = (summary.byAction[action] || 0) + 1;
      }
    });

    return summary;
  }, [selectedPermissions]);

  const getModuleDisplayName = (moduleKey: string) => {
    if (moduleKey === 'admin') return 'Admin';
    if (moduleKey === 'provider') return 'Provider';
    if (moduleKey === 'customer') return 'Customer';
    return moduleKey;
  };

  const selectedPermissionDetails = useMemo<SelectedPermissionDetail[]>(() => {
    return selectedPermissions
      .map(permissionName => {
        const parts = permissionName.split('.');
        const moduleKey = parts[0] || '';
        const sidebarKey = parts[1] || '';
        const entityKey = parts[2] || '';
        const action = parts[parts.length - 1] || '';

        const moduleNode = permissionTree[moduleKey];
        const sidebarNode = moduleNode?.children?.[sidebarKey];
        const entityNode = sidebarNode?.children?.[entityKey];

        const permission =
          moduleKey && sidebarKey && entityKey
            ? getPermissionsFromPath(permissionTree, [moduleKey, sidebarKey, entityKey]).find(
                item => item.name === permissionName
              )
            : undefined;

        return {
          key: permissionName,
          description: permission?.description || permissionName,
          action,
          moduleKey,
          moduleName: moduleNode?.name || getModuleDisplayName(moduleKey),
          sidebarName: sidebarNode?.name || sidebarKey,
          entityName: entityNode?.name || entityKey
        };
      })
      .sort((a, b) => a.key.localeCompare(b.key));
  }, [permissionTree, selectedPermissions]);

  const normalizedSummarySearch = summarySearch.trim().toLowerCase();

  const filteredSelectedPermissionDetails = useMemo(() => {
    return selectedPermissionDetails.filter(permission => {
      if (!normalizedSummarySearch) return true;

      const searchableValue = [
        permission.description,
        permission.key,
        permission.moduleName,
        permission.sidebarName,
        permission.entityName,
        permission.action
      ]
        .join(' ')
        .toLowerCase();

      return searchableValue.includes(normalizedSummarySearch);
    });
  }, [selectedPermissionDetails, normalizedSummarySearch]);

  const selectedInCurrentSectionCount = useMemo(() => {
    return currentContent.filter(item => selectedPermissions.includes(item.key)).length;
  }, [currentContent, selectedPermissions]);

  return {
    permissionSummary,
    selectedPermissionDetails,
    filteredSelectedPermissionDetails,
    selectedInCurrentSectionCount,
    hasSummaryFilter: normalizedSummarySearch.length > 0,
    getModuleDisplayName
  };
}
