/**
 * Modal de Visualización/Edición de Proveedores
 * 
 * Responsabilidades:
 * - Orquestar navegación por tabs
 * - Manejar estado del formulario y validación
 * - Controlar apertura/cierre del modal
 * - Guardar cambios del proveedor
 * 
 * Nota: Los componentes de cada tab están en shared/provider-details-tabs/
 * Basado en: PROVIDER_VISUALIZATION_MODAL_ANALYSIS.md
 */

'use client';

import { useState, useEffect } from 'react';
import { X } from 'lucide-react';
import type { Proveedor, Perfil } from '@/types/admin';
import {
  ProviderDatosBasicosTab,
  ProviderSucursalesTab,
  ProviderBancariaTab,
  ProviderLegalTab,
} from '@/components/admin/shared/provider-details-tabs';

// ============================================
// Props Interface
// ============================================

interface ProviderVisualizationModalProps {
  /** Controla si el modal está abierto */
  isOpen: boolean;
  /** Define si el modal es solo lectura (view) o editable (edit) */
  mode: 'view' | 'edit';
  /** Datos del proveedor a visualizar/editar */
  proveedor: Proveedor | null;
  /** Lista de perfiles disponibles para asignar */
  perfiles?: Perfil[];
  /** Callback al cerrar el modal */
  onClose: () => void;
  /** Callback al guardar cambios (solo en modo edit) */
  onSave?: (updatedProveedor: Proveedor) => void;
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
  { key: 'bancaria', label: 'Información Bancaria' },
  { key: 'legal', label: 'Legal' },
];

// ============================================
// Component
// ============================================

export function ProviderVisualizationModal({
  isOpen,
  mode,
  proveedor,
  perfiles = [],
  onClose,
  onSave,
}: ProviderVisualizationModalProps) {
  // Estado de navegación de tabs
  const [activeTab, setActiveTab] = useState<TabKey>('basicos');

  // Estado del formulario
  const [formData, setFormData] = useState<Proveedor | null>(null);

  // Estado de validación
  const [errors, setErrors] = useState<Record<string, string>>({});

  // Estado de guardado
  const [isSaving, setIsSaving] = useState(false);

  // Inicializar/resetear form data cuando cambia el proveedor
  useEffect(() => {
    if (proveedor) {
      setFormData({ ...proveedor });
    } else {
      setFormData(null);
    }
    setActiveTab('basicos');
    setErrors({});
  }, [proveedor, isOpen]);

  // Si el modal no está abierto, no renderizar
  if (!isOpen || !formData) return null;

  // Determinar si es modo solo lectura
  const isViewMode = mode === 'view';

  // ============================================
  // Handlers
  // ============================================

  /**
   * Cierra el modal y resetea estado
   */
  const handleClose = () => {
    setActiveTab('basicos');
    setErrors({});
    setFormData(null);
    onClose();
  };

  /**
   * Actualiza un campo del formulario
   */
  const handleFieldChange = (field: keyof Proveedor, value: unknown) => {
    if (!formData) return;
    
    setFormData({
      ...formData,
      [field]: value,
    });

    // Limpiar error del campo al editar
    if (errors[field]) {
      setErrors({
        ...errors,
        [field]: '',
      });
    }
  };

  /**
   * Valida el formulario antes de guardar
   * Retorna un objeto con {isValid, errors} para evitar problemas de closure
   */
  const validateForm = (): { isValid: boolean; errors: Record<string, string> } => {
    const newErrors: Record<string, string> = {};

    // Validaciones básicas
    if (!formData.nombreComercial?.trim()) {
      newErrors.nombreComercial = 'El nombre comercial es obligatorio';
    }

    if (!formData.nit?.trim()) {
      newErrors.nit = 'El NIT es obligatorio';
    }

    if (!formData.email?.trim()) {
      newErrors.email = 'El email es obligatorio';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      newErrors.email = 'El email no es válido';
    }

    if (!formData.telefono?.trim()) {
      newErrors.telefono = 'El teléfono es obligatorio';
    }

    // Validaciones de información bancaria (si existe)
    if (formData.informacionBancaria) {
      const infoBancaria = formData.informacionBancaria;

      if (!infoBancaria.titular?.trim()) {
        newErrors['informacionBancaria.titular'] = 'El titular de la cuenta es obligatorio';
      }

      if (!infoBancaria.banco?.trim()) {
        newErrors['informacionBancaria.banco'] = 'Debe seleccionar un banco';
      }

      if (!infoBancaria.numeroCuenta?.trim()) {
        newErrors['informacionBancaria.numeroCuenta'] = 'El número de cuenta es obligatorio';
      } else if (!/^\d+$/.test(infoBancaria.numeroCuenta)) {
        newErrors['informacionBancaria.numeroCuenta'] = 'El número de cuenta solo debe contener dígitos';
      } else if (infoBancaria.numeroCuenta.length < 8 || infoBancaria.numeroCuenta.length > 20) {
        newErrors['informacionBancaria.numeroCuenta'] = 'El número de cuenta debe tener entre 8 y 20 dígitos';
      }
    }

    setErrors(newErrors);
    return {
      isValid: Object.keys(newErrors).length === 0,
      errors: newErrors,
    };
  };

  /**
   * Guarda los cambios del formulario
   */
  const handleSave = async () => {
    const { isValid, errors: validationErrors } = validateForm();
    
    if (!isValid) {
      // Si hay errores en información bancaria, ir a esa tab
      const hasBasicErrors = Object.keys(validationErrors).some(key => !key.startsWith('informacionBancaria'));
      const hasBancariaErrors = Object.keys(validationErrors).some(key => key.startsWith('informacionBancaria'));
      
      if (hasBancariaErrors && !hasBasicErrors) {
        setActiveTab('bancaria');
      } else {
        setActiveTab('basicos');
      }
      return;
    }

    try {
      setIsSaving(true);
      if (onSave && formData) {
        await onSave(formData);
      }
      handleClose();
    } catch (error) {
      console.error('Error al guardar proveedor:', error);
      // TODO: Mostrar error toast
    } finally {
      setIsSaving(false);
    }
  };

  // ============================================
  // Render
  // ============================================

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
      <div className="bg-white rounded-[18px] shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-hidden flex flex-col">
        {/* Header */}
        <div className="flex items-center justify-between px-8 py-6 border-b border-[#E0E0E0]">
          <div>
            <h2 className="text-2xl font-semibold text-[#1A1A1A]">
              {isViewMode ? 'Ver Proveedor' : 'Editar Proveedor'}
            </h2>
            <p className="text-sm text-[#6A6A6A] mt-1">
              {formData.nombreComercial}
            </p>
          </div>
          <button
            onClick={handleClose}
            className="text-[#6A6A6A] hover:text-[#1A1A1A] transition-colors"
            aria-label="Cerrar modal"
          >
            <X size={24} />
          </button>
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

        {/* Tab Content */}
        <div className="flex-1 overflow-y-auto px-8 py-6">
          {activeTab === 'basicos' && (
            <ProviderDatosBasicosTab
              formData={formData}
              perfiles={perfiles}
              isViewMode={isViewMode}
              errors={errors}
              onFieldChange={handleFieldChange}
            />
          )}

          {activeTab === 'sucursales' && (
            <ProviderSucursalesTab
              formData={formData}
            />
          )}

          {activeTab === 'bancaria' && (
            <ProviderBancariaTab
              formData={formData}
              isViewMode={isViewMode}
              errors={errors}
              onFieldChange={handleFieldChange}
            />
          )}

          {activeTab === 'legal' && (
            <ProviderLegalTab
              formData={formData}
            />
          )}
        </div>

        {/* Footer */}
        <div className="flex items-center justify-end gap-3 px-8 py-6 border-t border-[#E0E0E0]">
          <button
            onClick={handleClose}
            className="px-6 h-[44px] border border-[#E0E0E0] text-[#1A1A1A] rounded-xl hover:bg-[#F7F7F7] transition-colors"
          >
            {isViewMode ? 'Cerrar' : 'Cancelar'}
          </button>

          {!isViewMode && (
            <button
              onClick={handleSave}
              disabled={isSaving}
              className="px-6 h-[44px] bg-[#4B236A] text-white rounded-xl hover:bg-[#5D2B7D] transition-colors shadow-lg hover:shadow-xl disabled:opacity-50 disabled:cursor-not-allowed"
            >
              {isSaving ? 'Guardando...' : 'Guardar Cambios'}
            </button>
          )}
        </div>
      </div>
    </div>
  );
}

