/**
 * Selector avanzado de permisos con navegación 4 niveles
 */

'use client';

import { useState } from 'react';
import { ChevronRight, ChevronLeft, FolderOpen, Folder, Users, Settings, BarChart3, ShieldCheck } from 'lucide-react';
import { PermissionTree, PermissionNavigationState } from '../../../types/role-form-types';
import { getPermissionsFromPath } from '../../../utils/permission-tree-builder';

interface PermissionTreeViewProps {
  permissionTree: PermissionTree;
  selectedPermissions: string[];
  onPermissionToggle: (permissionName: string) => void;
  disabled?: boolean;
}

export function PermissionTreeView({
  permissionTree,
  selectedPermissions,
  onPermissionToggle,
  disabled = false
}: PermissionTreeViewProps) {
  const [navigationState, setNavigationState] = useState<PermissionNavigationState>({
    currentPath: [],
    selectedPermissions: new Set(selectedPermissions),
    permissionTree
  });

  // Obtener icono para módulo
  const getModuleIcon = (moduleKey: string) => {
    switch (moduleKey) {
      case 'admin': return <ShieldCheck className="w-5 h-5 text-[#4B236A]" />;
      case 'provider': return <Users className="w-5 h-5 text-[#10B981]" />;
      case 'customer': return <Users className="w-5 h-5 text-[#3B82F6]" />;
      default: return <Settings className="w-5 h-5 text-[#6A6A6A]" />;
    }
  };

  // Obtener icono para sidebar group
  const getSidebarIcon = (sidebarKey: string) => {
    switch (sidebarKey) {
      case 'profiles': return <Users className="w-4 h-4 text-[#4B236A]" />;
      case 'parametrization': return <Settings className="w-4 h-4 text-[#4B236A]" />;
      case 'marketing': return <BarChart3 className="w-4 h-4 text-[#4B236A]" />;
      default: return <FolderOpen className="w-4 h-4 text-[#4B236A]" />;
    }
  };

  // Navegar hacia un nivel
  const navigateTo = (level: string) => {
    setNavigationState(prev => ({
      ...prev,
      currentPath: [...prev.currentPath, level]
    }));
  };

  // Navegar hacia atrás
  const navigateBack = () => {
    setNavigationState(prev => ({
      ...prev,
      currentPath: prev.currentPath.slice(0, -1)
    }));
  };

  // Ir al inicio
  const navigateHome = () => {
    setNavigationState(prev => ({
      ...prev,
      currentPath: []
    }));
  };

  // Obtener contenido actual
  const getCurrentContent = () => {
    const { currentPath } = navigationState;
    
    if (currentPath.length === 0) {
      // Nivel 1: Módulos
      return Object.keys(permissionTree).map(moduleKey => ({
        key: moduleKey,
        name: permissionTree[moduleKey].name,
        type: 'module' as const,
        icon: getModuleIcon(moduleKey)
      }));
    }
    
    if (currentPath.length === 1) {
      // Nivel 2: Sidebar Groups
      const moduleKey = currentPath[0];
      const treeModule = permissionTree[moduleKey];
      return Object.keys(treeModule.children || {}).map(sidebarKey => ({
        key: sidebarKey,
        name: treeModule.children![sidebarKey].name,
        type: 'sidebar' as const,
        icon: getSidebarIcon(sidebarKey)
      }));
    }
    
    if (currentPath.length === 2) {
      // Nivel 3: Entidades
      const [moduleKey, sidebarKey] = currentPath;
      const sidebar = permissionTree[moduleKey].children![sidebarKey];
      return Object.keys(sidebar.children || {}).map(entityKey => ({
        key: entityKey,
        name: sidebar.children![entityKey].name,
        type: 'entity' as const,
        icon: <Folder className="w-4 h-4 text-[#4B236A]" />
      }));
    }
    
    if (currentPath.length === 3) {
      // Nivel 4: Permisos (acciones)
      const permissions = getPermissionsFromPath(permissionTree, currentPath);
      return permissions.map(permission => ({
        key: permission.name,
        name: permission.description,
        type: 'permission' as const,
        action: permission.action,
        permission
      }));
    }
    
    return [];
  };

  // Obtener breadcrumb
  const getBreadcrumb = () => {
    const breadcrumb = ['Permisos'];
    
    if (navigationState.currentPath.length > 0) {
      breadcrumb.push(permissionTree[navigationState.currentPath[0]]?.name || '');
    }
    
    if (navigationState.currentPath.length > 1) {
      const moduleKey = navigationState.currentPath[0];
      const sidebarKey = navigationState.currentPath[1];
      breadcrumb.push(permissionTree[moduleKey].children![sidebarKey]?.name || '');
    }
    
    if (navigationState.currentPath.length > 2) {
      const [moduleKey, sidebarKey, entityKey] = navigationState.currentPath;
      breadcrumb.push(permissionTree[moduleKey].children![sidebarKey].children![entityKey]?.name || '');
    }
    
    return breadcrumb;
  };

  // Obtener resumen detallado de permisos seleccionados
  const getPermissionSummary = () => {
    const summary = {
      total: selectedPermissions.length,
      byModule: {} as Record<string, number>,
      byAction: {} as Record<string, number>
    };

    selectedPermissions.forEach(permissionName => {
      // Extraer módulo y acción del permiso (formato: admin.profiles.roles.create)
      const parts = permissionName.split('.');
      if (parts.length >= 4) {
        const moduleKey = parts[0];
        const action = parts[parts.length - 1];
        
        summary.byModule[moduleKey] = (summary.byModule[moduleKey] || 0) + 1;
        summary.byAction[action] = (summary.byAction[action] || 0) + 1;
      }
    });

    return summary;
  };

  const currentContent = getCurrentContent();
  const breadcrumb = getBreadcrumb();
  const canGoBack = navigationState.currentPath.length > 0;
  const isAtPermissionLevel = navigationState.currentPath.length === 3;
  const permissionSummary = getPermissionSummary();

  return (
    <div className="border border-[#E0E0E0] rounded-[14px] p-4 space-y-4">
      {/* Breadcrumb Navigation */}
      <div className="flex items-center justify-between">
        <div className="flex items-center space-x-2 text-sm">
          {breadcrumb.map((crumb, index) => (
            <div key={index} className="flex items-center space-x-2">
              {index > 0 && <ChevronRight className="w-3 h-3 text-[#6A6A6A]" />}
              <span 
                className={index === breadcrumb.length - 1 
                  ? "text-[#1A1A1A] font-medium" 
                  : "text-[#6A6A6A] hover:text-[#4B236A] cursor-pointer"
                }
                onClick={() => {
                  if (index === 0) navigateHome();
                  else if (index === 1) setNavigationState(prev => ({ ...prev, currentPath: [prev.currentPath[0]] }));
                  else if (index === 2) setNavigationState(prev => ({ ...prev, currentPath: prev.currentPath.slice(0, 2) }));
                }}
              >
                {crumb}
              </span>
            </div>
          ))}
        </div>
        
        {canGoBack && (
          <button
            onClick={navigateBack}
            disabled={disabled}
            className="flex items-center space-x-1 text-sm text-[#4B236A] hover:text-[#5D2B7D] transition-colors disabled:opacity-50"
          >
            <ChevronLeft className="w-4 h-4" />
            <span>Atrás</span>
          </button>
        )}
      </div>

      {/* Content */}
      <div className="space-y-2 max-h-64 overflow-y-auto">
        {currentContent.map((item) => (
          <div
            key={item.key}
            className={`flex items-center justify-between p-3 rounded-xl border transition-all ${
              item.type === 'permission' 
                ? 'border-[#E0E0E0] hover:border-[#4B236A]/20 hover:bg-[#4B236A]/5' 
                : 'border-[#E0E0E0] hover:border-[#4B236A] hover:bg-[#4B236A]/5 cursor-pointer'
            }`}
            onClick={() => {
              if (item.type !== 'permission' && !disabled) {
                navigateTo(item.key);
              }
            }}
          >
            <div className="flex items-center space-x-3">
              {item.type !== 'permission' ? item.icon : <Settings className="w-4 h-4 text-[#4B236A]" />}
              <div>
                <p className="text-sm font-medium text-[#1A1A1A]">
                  {item.name}
                </p>
                {item.type === 'permission' && (
                  <p className="text-xs text-[#6A6A6A] font-mono">
                    {item.key}
                  </p>
                )}
              </div>
            </div>

            <div className="flex items-center space-x-2">
              {item.type === 'permission' ? (
                <input
                  type="checkbox"
                  checked={selectedPermissions.includes(item.key)}
                  onChange={() => onPermissionToggle(item.key)}
                  disabled={disabled}
                  className="w-4 h-4 text-[#4B236A] rounded focus:ring-2 focus:ring-[#4B236A]"
                />
              ) : (
                <ChevronRight className="w-4 h-4 text-[#6A6A6A]" />
              )}
            </div>
          </div>
        ))}
      </div>

      {/* Summary */}
      {isAtPermissionLevel && (
        <div className="pt-3 border-t border-[#E0E0E0] space-y-2">
          <p className="text-xs text-[#6A6A6A]">
            {currentContent.filter(item => selectedPermissions.includes(item.key)).length} de {currentContent.length} permisos seleccionados en esta sección
          </p>
          
          {permissionSummary.total > 0 && (
            <div className="bg-[#F7F7F7] rounded-lg p-2 space-y-1">
              <p className="text-xs font-medium text-[#1A1A1A]">
                Total seleccionados: {permissionSummary.total}
              </p>
              <div className="flex flex-wrap gap-2 text-xs text-[#6A6A6A]">
                {Object.entries(permissionSummary.byModule).map(([moduleKey, count]) => (
                  <span key={moduleKey} className="bg-white px-2 py-1 rounded border">
                    {moduleKey === 'admin' ? 'Admin' : moduleKey === 'provider' ? 'Provider' : 'Customer'}: {count}
                  </span>
                ))}
              </div>
            </div>
          )}
        </div>
      )}
      
      {!isAtPermissionLevel && (
        <div className="pt-3 border-t border-[#E0E0E0] space-y-2">
          {permissionSummary.total === 0 ? (
            <p className="text-xs text-[#6A6A6A]">
              Ningún permiso seleccionado
            </p>
          ) : (
            <>
              <div className="flex items-center justify-between">
                <p className="text-xs font-medium text-[#1A1A1A]">
                  {permissionSummary.total} permiso{permissionSummary.total !== 1 ? 's' : ''} seleccionado{permissionSummary.total !== 1 ? 's' : ''}
                </p>
                {Object.keys(permissionSummary.byModule).length > 1 && (
                  <span className="text-xs text-[#4B236A] bg-[#4B236A]/10 px-2 py-1 rounded">
                    {Object.keys(permissionSummary.byModule).length} módulos
                  </span>
                )}
              </div>
              
              <div className="space-y-1">
                {/* Desglose por módulo */}
                <div className="flex flex-wrap gap-1">
                  {Object.entries(permissionSummary.byModule).map(([moduleKey, count]) => (
                    <span key={moduleKey} className="text-xs bg-white border border-[#E0E0E0] px-2 py-1 rounded text-[#6A6A6A]">
                      {moduleKey === 'admin' ? '👤 Admin' : moduleKey === 'provider' ? '🏪 Provider' : '🛒 Customer'}: {count}
                    </span>
                  ))}
                </div>
                
                {/* Desglose por tipo de acción */}
                {Object.keys(permissionSummary.byAction).length > 0 && (
                  <div className="flex flex-wrap gap-1">
                    {Object.entries(permissionSummary.byAction).map(([action, count]) => (
                      <span key={action} className="text-xs bg-[#4B236A]/5 text-[#4B236A] px-2 py-1 rounded">
                        {action === 'create' ? '➕' : action === 'view' ? '👁️' : action === 'edit' ? '✏️' : action === 'delete' ? '🗑️' : '⚙️'} {action}: {count}
                      </span>
                    ))}
                  </div>
                )}
              </div>
            </>
          )}
        </div>
      )}
    </div>
  );
}