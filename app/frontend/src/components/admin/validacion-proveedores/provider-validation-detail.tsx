/**
 * Área de Detalle de Validación de Proveedor
 * 
 * Responsabilidades:
 * - Mostrar header con nombre del proveedor y estado
 * - Renderizar tabs (reutiliza tabs compartidos)
 * - Integrar sección de comentarios
 * - Mostrar acciones de aprobación/rechazo
 * - Callback opcionales para notificaciones de éxito/error
 * 
 * Reutiliza:
 * - ProviderDatosBasicosTab
 * - ProviderSucursalesTab
 * - ProviderBancariaTab
 * - ProviderLegalTab
 * - ProviderValidationActions (con hook interno de aprobación)
 */

'use client';

import { useCallback, useState } from 'react';
import type { Proveedor, Perfil } from '@/types/admin';
import {
  ProviderDatosBasicosTab,
  ProviderSucursalesTab,
  ProviderBancariaTab,
  ProviderLegalTab,
} from '@/components/admin/shared/provider-details-tabs';
import { formatDateDDMMYYYY } from '@/lib/utils/date';
import { ProviderValidationActions } from './provider-validation-actions';
import { ProviderValidationComments } from './provider-validation-comments';

// ============================================
// Props Interface
// ============================================

interface ProviderValidationDetailProps {
  provider: Proveedor;
  onApprovalSuccess?: (message: string) => void;
  onApprovalError?: (error: string) => void;
}

// ============================================
// Tipos de Tab
// ============================================

type TabKey = 'basicos' | 'sucursales' | 'bancaria' | 'legal';

interface Tab {
  key: TabKey;
  label: string;
}

const TABS: Tab[] = [
  { key: 'basicos', label: 'Datos Básicos' },
  { key: 'sucursales', label: 'Sucursales' },
  { key: 'bancaria', label: 'Info. Bancaria' },
  { key: 'legal', label: 'Legal' },
];

// ============================================
// Component
// ============================================

export function ProviderValidationDetail({
  provider,
  onApprovalSuccess,
  onApprovalError,
}: ProviderValidationDetailProps) {
  const [activeTab, setActiveTab] = useState<TabKey>('basicos');
  const [commentsRefreshTrigger, setCommentsRefreshTrigger] = useState(0);

  const handleCommentCreated = useCallback(() => {
    setCommentsRefreshTrigger((prev) => prev + 1);
  }, []);

  const verificationBadge =
    provider.verificado === 3
      ? {
          containerClass: 'bg-[#4B236A]/10 text-[#4B236A]',
          dotClass: 'bg-[#4B236A]',
          label: 'Por aprobar nuevamente',
        }
      : {
          containerClass: 'bg-orange-100 text-orange-700',
          dotClass: 'bg-orange-500',
          label: 'Pendiente',
        };

  // Mock de perfiles (TODO: obtener desde API o props)
  const perfiles: Perfil[] = [];

  return (
    <div className="h-full flex flex-col bg-white rounded-[18px] border border-[#E0E0E0] shadow-sm">
      {/* Header */}
      <div className="px-8 py-6 border-b border-[#E0E0E0]">
        <div className="flex items-start justify-between">
          <div>
            <h2 className="text-2xl font-semibold text-[#1A1A1A] mb-1">
              {provider.nombreComercial}
            </h2>
            <p className="text-sm text-[#6A6A6A]">
              {provider.tipoEstablecimiento || 'Restaurante'} • Solicitud: {formatDateDDMMYYYY(provider.fechaCreacion, { emptyFallback: 'N/A' })}
            </p>
          </div>
          
          {/* Badge de estado */}
          <div className={`px-4 py-2 rounded-full text-sm font-medium flex items-center gap-2 ${verificationBadge.containerClass}`}>
            <span className={`w-2 h-2 rounded-full ${verificationBadge.dotClass}`}></span>
            {verificationBadge.label}
          </div>
        </div>
      </div>

      {/* Tab Navigation */}
      <div className="flex gap-2 px-8 pt-6 border-b border-[#E0E0E0]">
        {TABS.map((tab) => (
          <button
            key={tab.key}
            onClick={() => setActiveTab(tab.key)}
            className={`px-4 py-2 text-sm font-medium transition-colors ${
              activeTab === tab.key
                ? 'border-b-2 border-[#4B236A] text-[#4B236A]'
                : 'text-[#6A6A6A] hover:text-[#1A1A1A]'
            }`}
          >
            {tab.label}
          </button>
        ))}
      </div>

      {/* Content Area (scrollable) */}
      <div className="flex-1 overflow-y-auto px-8 py-6 space-y-6">
        {/* Tab Content */}
        <div>
          {activeTab === 'basicos' && (
            <ProviderDatosBasicosTab
              formData={provider}
              perfiles={perfiles}
              isViewMode={true}  // Siempre en modo lectura para validación
              errors={{}}
              onFieldChange={() => {}}  // No-op en modo validación
            />
          )}

          {activeTab === 'sucursales' && (
            <ProviderSucursalesTab formData={provider} />
          )}

          {activeTab === 'bancaria' && (
            <ProviderBancariaTab
              formData={provider}
              isViewMode={true}
              errors={{}}
              onFieldChange={() => {}}
            />
          )}

          {activeTab === 'legal' && (
            <ProviderLegalTab formData={provider} />
          )}
        </div>

        {/* Historial y captura de comentarios */}
        <div className="pt-2 border-t border-[#E0E0E0]">
          <ProviderValidationComments
            providerId={provider.id}
            refreshTrigger={commentsRefreshTrigger}
          />
        </div>
      </div>

      {/* Footer con acciones */}
      <div className="px-8 py-6 border-t border-[#E0E0E0]">
        <ProviderValidationActions
          providerId={provider.id}
          onValidationCommentCreated={handleCommentCreated}
          onApprovalSuccess={onApprovalSuccess}
          onApprovalError={onApprovalError}
        />
      </div>
    </div>
  );
}
