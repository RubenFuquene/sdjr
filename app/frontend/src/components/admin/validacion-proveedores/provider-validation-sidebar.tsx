/**
 * Sidebar de Proveedores Pendientes
 * 
 * Responsabilidades:
 * - Mostrar lista de proveedores con estado "pendiente"
 * - Indicar visualmente el proveedor seleccionado
 * - Mostrar información básica (nombre, tipo, fecha solicitud)
 * - Filtrar/buscar proveedores (futuro)
 * 
 * Basado en diseño Figma: Sidebar izquierdo de ValidacionProveedores.tsx
 */

'use client';

import { useEffect } from 'react';
import { Clock } from 'lucide-react';
import type { Proveedor, ProveedorListItem } from '@/types/admin';
import { useCommerceManagement } from '@/hooks/use-commerce-management';
import { TableLoadingState } from '@/components/admin/shared/loading-state';
import { ErrorState } from '@/components/admin/shared/error-state';
import { formatDateDDMMYYYY } from '@/lib/utils/date';

// ============================================
// Props Interface
// ============================================

interface ProviderValidationSidebarProps {
  selectedProviderId?: number;
  onSelectProvider: (provider: Proveedor) => void;
  refreshTrigger?: number;
}

// ============================================
// Component
// ============================================

export function ProviderValidationSidebar({
  selectedProviderId,
  onSelectProvider,
  refreshTrigger = 0,
}: ProviderValidationSidebarProps) {
  // Inicializar hook con filtro para mostrar pendientes y por aprobar nuevamente
  const commerceManagement = useCommerceManagement({ verified: '0,3' });
  const { refresh } = commerceManagement;

  // Mostrar proveedores con estado pendiente (0) y por aprobar nuevamente (3)
  const pendingProviders = commerceManagement.commerces;

  useEffect(() => {
    if (refreshTrigger === 0) {
      return;
    }

    void refresh();
  }, [refresh, refreshTrigger]);

  // Estados
  if (commerceManagement.error) {
    return (
      <div className="h-full flex items-center justify-center">
        <ErrorState 
          message={commerceManagement.error} 
          onRetry={refresh} 
        />
      </div>
    );
  }

  if (commerceManagement.loading) {
    return (
      <div className="h-full">
        <TableLoadingState />
      </div>
    );
  }

  return (
    <div className="h-full flex flex-col bg-white rounded-[18px] border border-[#E0E0E0] shadow-sm">
      {/* Header */}
      <div className="p-6 border-b border-[#E0E0E0]">
        <h2 className="text-xl font-semibold text-[#1A1A1A] mb-1">
          Proveedores Pendientes
        </h2>
        <p className="text-sm text-[#6A6A6A]">
          Revisa y aprueba solicitudes de proveedores
        </p>
      </div>

      {/* Lista de Proveedores */}
      <div className="flex-1 overflow-y-auto p-4 space-y-3">
        {pendingProviders.length === 0 ? (
          <div className="flex flex-col items-center justify-center py-12 text-center">
            <Clock className="w-12 h-12 text-[#6A6A6A] mb-3" />
            <p className="text-sm font-medium text-[#1A1A1A] mb-1">
              No hay proveedores pendientes
            </p>
            <p className="text-xs text-[#6A6A6A]">
              Las solicitudes aparecerán aquí
            </p>
          </div>
        ) : (
          pendingProviders.map((provider) => (
            <ProviderPendingCard
              key={provider.id}
              provider={provider}
              isSelected={provider.id === selectedProviderId}
              onClick={async () => {
                // Fetch proveedor completo desde hook centralizado
                try {
                  const fullProvider = await commerceManagement.fetchCommerceById(provider.id);
                  onSelectProvider(fullProvider);
                } catch (error) {
                  console.error('Error al cargar proveedor:', error);
                }
              }}
            />
          ))
        )}
      </div>
    </div>
  );
}

// ============================================
// Helper Components
// ============================================

/**
 * Card individual de proveedor pendiente
 */
interface ProviderPendingCardProps {
  provider: ProveedorListItem;
  isSelected: boolean;
  onClick: () => void | Promise<void>;
}

function ProviderPendingCard({ provider, isSelected, onClick }: ProviderPendingCardProps) {
  const statusMeta =
    provider.estadoVerificacion === 3
      ? {
          iconClass: 'text-[#4B236A]',
          badgeClass: 'bg-[#4B236A]/10 text-[#4B236A]',
          dotClass: 'bg-[#4B236A]',
          label: 'Por aprobar nuevamente',
        }
      : {
          iconClass: 'text-orange-500',
          badgeClass: 'bg-orange-100 text-orange-700',
          dotClass: 'bg-orange-500',
          label: 'Pendiente',
        };

  return (
    <button
      onClick={onClick}
      className={`w-full text-left p-4 rounded-xl border-2 transition-all ${
        isSelected
          ? 'border-[#4B236A] bg-[#4B236A]/5 shadow-md'
          : 'border-[#E0E0E0] bg-white hover:border-[#4B236A]/30 hover:bg-[#F7F7F7]'
      }`}
    >
      <div className="flex items-start justify-between mb-2">
        <h3 className="text-sm font-semibold text-[#1A1A1A] line-clamp-1">
          {provider.nombreComercial}
        </h3>
        <div className="flex-shrink-0 ml-2">
          <Clock className={`w-4 h-4 ${statusMeta.iconClass}`} />
        </div>
      </div>

      <p className="text-xs text-[#6A6A6A] mb-2">
        {provider.tipoEstablecimiento || 'Establecimiento'}
      </p>

      <div className="mb-3">
        <span className={`inline-flex items-center gap-2 px-2.5 py-1 rounded-full text-[11px] font-medium ${statusMeta.badgeClass}`}>
          <span className={`w-1.5 h-1.5 rounded-full ${statusMeta.dotClass}`}></span>
          {statusMeta.label}
        </span>
      </div>

      <div className="flex items-center justify-between text-xs text-[#6A6A6A]">
        <span>Solicitud:</span>
        <span className="font-medium">
          {formatDateDDMMYYYY(provider.createdAt, {
            emptyFallback: 'Sin fecha',
            invalidFallback: 'Fecha inválida',
          })}
        </span>
      </div>
    </button>
  );
}
