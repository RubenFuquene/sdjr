/**
 * Área de Detalle de Validación de Proveedor
 * 
 * Responsabilidades:
 * - Mostrar header con nombre del proveedor y estado
 * - Renderizar tabs (reutiliza tabs compartidos)
 * - Integrar sección de comentarios
 * - Mostrar acciones de aprobación/rechazo
 * 
 * Reutiliza:
 * - ProviderDatosBasicosTab
 * - ProviderSucursalesTab
 * - ProviderBancariaTab
 * - ProviderLegalTab
 */

'use client';

import { useState } from 'react';
import type { Proveedor, Perfil } from '@/types/admin';
import {
  ProviderDatosBasicosTab,
  ProviderSucursalesTab,
  ProviderBancariaTab,
  ProviderLegalTab,
} from '@/components/admin/shared/provider-details-tabs';
import { ProviderValidationComments } from './provider-validation-comments';
import { ProviderValidationActions } from './provider-validation-actions';

// ============================================
// Props Interface
// ============================================

interface ProviderValidationDetailProps {
  provider: Proveedor;
  onApprove: (providerId: number) => Promise<void>;
  onReject: (providerId: number, reason: string) => Promise<void>;
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
  onApprove,
  onReject,
}: ProviderValidationDetailProps) {
  const [activeTab, setActiveTab] = useState<TabKey>('basicos');

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
              {provider.tipoEstablecimiento || 'Restaurante'} • Solicitud: 2025-11-15
            </p>
          </div>
          
          {/* Badge de estado */}
          <div className="px-4 py-2 bg-orange-100 text-orange-700 rounded-full text-sm font-medium flex items-center gap-2">
            <span className="w-2 h-2 bg-orange-500 rounded-full"></span>
            Pendiente
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

        {/* Comments Section */}
        <ProviderValidationComments providerId={provider.id} />
      </div>

      {/* Footer con acciones */}
      <div className="px-8 py-6 border-t border-[#E0E0E0]">
        <ProviderValidationActions
          providerId={provider.id}
          onApprove={onApprove}
          onReject={onReject}
        />
      </div>
    </div>
  );
}
