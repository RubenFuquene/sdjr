/**
 * Layout Principal de Validación de Proveedores
 * 
 * Responsabilidades:
 * - Orquestar sidebar (lista de pendientes) + área de detalle
 * - Manejar selección de proveedor activo
 * - Coordinar estado entre sidebar y detalle
 * - Callbacks opcionales para notificaciones de aprobación/rechazo
 * 
 * Nota: La aprobación/rechazo se maneja internamente en ProviderValidationActions
 * con el hook useCommerceApproval()
 * 
 * Basado en diseño Figma: ValidacionProveedores.tsx
 * Patrón: Layout con estado compartido
 */

'use client';

import { useState, useCallback } from 'react';
import type { Proveedor } from '@/types/admin';
import { ProviderValidationSidebar } from './provider-validation-sidebar';
import { ProviderValidationDetail } from './provider-validation-detail';

// ============================================
// Component
// ============================================

export function ProviderValidationLayout() {
  // Estado del proveedor seleccionado
  const [selectedProvider, setSelectedProvider] = useState<Proveedor | null>(null);

  /**
   * Handler cuando se selecciona un proveedor del sidebar
   */
  const handleSelectProvider = useCallback((provider: Proveedor) => {
    setSelectedProvider(provider);
  }, []);

  /**
   * Callback cuando se aprueba un proveedor
   */
  const handleApprovalSuccess = useCallback((message: string) => {
    console.log('Success:', message);
    // Limpiar selección después de aprobación
    setSelectedProvider(null);
  }, []);

  /**
   * Callback cuando hay error en aprobación/rechazo
   */
  const handleApprovalError = useCallback((error: string) => {
    console.error('Error en aprobación:', error);
    // El error se muestra en el toast/banner del componente de acciones
  }, []);

  return (
    <div className="flex h-[calc(100vh-80px)] gap-6">
      {/* Sidebar - Lista de Proveedores Pendientes */}
      <aside className="w-96 flex-shrink-0">
        <ProviderValidationSidebar
          selectedProviderId={selectedProvider?.id}
          onSelectProvider={handleSelectProvider}
        />
      </aside>

      {/* Área de Detalle */}
      <main className="flex-1 overflow-hidden">
        {selectedProvider ? (
          <ProviderValidationDetail
            provider={selectedProvider}
            onApprovalSuccess={handleApprovalSuccess}
            onApprovalError={handleApprovalError}
          />
        ) : (
          <div className="flex items-center justify-center h-full">
            <div className="text-center">
              <p className="text-xl font-medium text-[#6A6A6A] mb-2">
                Selecciona un proveedor
              </p>
              <p className="text-sm text-[#6A6A6A]">
                Elige un proveedor de la lista para ver sus detalles
              </p>
            </div>
          </div>
        )}
      </main>
    </div>
  );
}
