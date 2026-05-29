/**
 * Selector avanzado de permisos con navegación 4 niveles
 */

'use client';

import { useState } from 'react';
import { ChevronRight, ChevronLeft, FolderOpen, Folder, Users, Settings, BarChart3, ShieldCheck } from 'lucide-react';
import { PermissionTree } from '../../../types/role-form-types';
import { usePermissionTreeNavigation } from './hooks/use-permission-tree-navigation';
import { usePermissionSummary } from './hooks/use-permission-summary';

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
  const [summarySearch, setSummarySearch] = useState('');
  const {
    currentContent,
    breadcrumb,
    canGoBack,
    isAtPermissionLevel,
    navigateTo,
    navigateBack,
    navigateToBreadcrumbIndex
  } = usePermissionTreeNavigation({ permissionTree });

  const {
    permissionSummary,
    selectedPermissionDetails,
    filteredSelectedPermissionDetails,
    selectedInCurrentSectionCount,
    hasSummaryFilter,
    getModuleDisplayName
  } = usePermissionSummary({
    permissionTree,
    selectedPermissions,
    summarySearch,
    currentContent
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
                onClick={() => navigateToBreadcrumbIndex(index)}
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
              {item.type === 'module' && getModuleIcon(item.key)}
              {item.type === 'sidebar' && getSidebarIcon(item.key)}
              {item.type === 'entity' && <Folder className="w-4 h-4 text-[#4B236A]" />}
              {item.type === 'permission' && <Settings className="w-4 h-4 text-[#4B236A]" />}
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
            {selectedInCurrentSectionCount} de {currentContent.length} permisos seleccionados en esta sección
          </p>
          
          {permissionSummary.total > 0 && (
            <div className="bg-[#F7F7F7] rounded-lg p-2 space-y-2">
              <p className="text-xs font-medium text-[#1A1A1A]">
                Total seleccionados: {permissionSummary.total}
              </p>
              <input
                type="text"
                value={summarySearch}
                onChange={(event) => setSummarySearch(event.target.value)}
                placeholder="Buscar por permiso, módulo o acción"
                disabled={disabled}
                className="w-full h-9 rounded-[10px] border border-[#E0E0E0] bg-white px-3 text-xs text-[#1A1A1A] placeholder:text-[#6A6A6A] focus:outline-none focus:ring-2 focus:ring-[#4B236A]/30 disabled:opacity-50"
              />

              {hasSummaryFilter && (
                <p className="text-xs text-[#6A6A6A]">
                  Mostrando {filteredSelectedPermissionDetails.length} de {selectedPermissionDetails.length} permisos
                </p>
              )}

              <div className="flex flex-wrap gap-2 text-xs text-[#6A6A6A]">
                {Object.entries(permissionSummary.byModule).map(([moduleKey, count]) => (
                  <span key={moduleKey} className="bg-white px-2 py-1 rounded border">
                    {getModuleDisplayName(moduleKey)}: {count}
                  </span>
                ))}
              </div>

              <div className="space-y-1">
                <p className="text-xs font-medium text-[#1A1A1A]">
                  Permisos del rol
                </p>
                {filteredSelectedPermissionDetails.length === 0 ? (
                  <p className="text-xs text-[#6A6A6A]">
                    No hay permisos que coincidan con la búsqueda.
                  </p>
                ) : (
                  <div className="max-h-44 overflow-y-auto space-y-1 pr-1">
                    {filteredSelectedPermissionDetails.map(permission => (
                      <div key={permission.key} className="bg-white border border-[#E0E0E0] rounded p-2 space-y-1">
                        <p className="text-xs font-medium text-[#1A1A1A]">
                          {permission.description}
                        </p>
                        <p className="text-[11px] text-[#6A6A6A]">
                          {permission.moduleName} / {permission.sidebarName} / {permission.entityName}
                        </p>
                        <p className="text-[11px] text-[#6A6A6A] font-mono break-all">
                          {permission.key}
                        </p>
                      </div>
                    ))}
                  </div>
                )}
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
                <p className="text-xs font-medium text-[#1A1A1A]">
                  Permisos del rol
                </p>
                <input
                  type="text"
                  value={summarySearch}
                  onChange={(event) => setSummarySearch(event.target.value)}
                  placeholder="Buscar por permiso, módulo o acción"
                  disabled={disabled}
                  className="w-full h-9 rounded-[10px] border border-[#E0E0E0] bg-white px-3 text-xs text-[#1A1A1A] placeholder:text-[#6A6A6A] focus:outline-none focus:ring-2 focus:ring-[#4B236A]/30 disabled:opacity-50"
                />

                {hasSummaryFilter && (
                  <p className="text-xs text-[#6A6A6A]">
                    Mostrando {filteredSelectedPermissionDetails.length} de {selectedPermissionDetails.length} permisos
                  </p>
                )}

                {filteredSelectedPermissionDetails.length === 0 ? (
                  <p className="text-xs text-[#6A6A6A]">
                    No hay permisos que coincidan con la búsqueda.
                  </p>
                ) : (
                  <>
                    <div className="max-h-32 overflow-y-auto space-y-1 pr-1">
                      {filteredSelectedPermissionDetails.slice(0, 6).map(permission => (
                        <div key={permission.key} className="bg-white border border-[#E0E0E0] rounded p-2">
                          <p className="text-xs text-[#1A1A1A]">
                            {permission.description}
                          </p>
                          <p className="text-[11px] text-[#6A6A6A] font-mono break-all">
                            {permission.key}
                          </p>
                        </div>
                      ))}
                    </div>
                    {filteredSelectedPermissionDetails.length > 6 && (
                      <p className="text-xs text-[#6A6A6A]">
                        +{filteredSelectedPermissionDetails.length - 6} permisos adicionales
                      </p>
                    )}
                  </>
                )}
              </div>
            </>
          )}
        </div>
      )}
    </div>
  );
}