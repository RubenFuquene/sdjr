/**
 * Modal de Visualización/Edición de Proveedores
 * 
 * Responsabilidades:
 * - Visualizar información completa del proveedor (modo lectura)
 * - Editar información del proveedor (modo edición)
 * - Navegación por tabs: Datos Básicos, Sucursales, Información Bancaria, Legal
 * 
 * Basado en: PROVIDER_VISUALIZATION_MODAL_ANALYSIS.md
 */

'use client';

import { useState, useEffect } from 'react';
import { X, FileText, Download } from 'lucide-react';
import type { Proveedor, Perfil, DocumentoProveedor } from '@/types/admin';

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
   */
  const validateForm = (): boolean => {
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

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  /**
   * Guarda los cambios del formulario
   */
  const handleSave = async () => {
    if (!validateForm()) {
      setActiveTab('basicos'); // Volver a tab de datos básicos si hay errores
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
            <DatosBasicosTab
              formData={formData}
              perfiles={perfiles}
              isViewMode={isViewMode}
              errors={errors}
              onFieldChange={handleFieldChange}
            />
          )}

          {activeTab === 'sucursales' && (
            <SucursalesTab
              formData={formData}
              isViewMode={isViewMode}
              onFieldChange={handleFieldChange}
            />
          )}

          {activeTab === 'bancaria' && (
            <BancariaTab
              formData={formData}
              isViewMode={isViewMode}
              errors={errors}
              onFieldChange={handleFieldChange}
            />
          )}

          {activeTab === 'legal' && (
            <LegalTab
              formData={formData}
              isViewMode={isViewMode}
              onFieldChange={handleFieldChange}
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

// ============================================
// Tab Components (Placeholders)
// ============================================

interface TabProps {
  formData: Proveedor;
  isViewMode: boolean;
  onFieldChange: (field: keyof Proveedor, value: unknown) => void;
}

interface DatosBasicosTabProps extends TabProps {
  perfiles: Perfil[];
  errors: Record<string, string>;
}

/**
 * Tab: Datos Básicos
 * Formulario de 2 columnas con 10 campos + sección de documentos
 */
function DatosBasicosTab({ formData, perfiles, isViewMode, errors, onFieldChange }: DatosBasicosTabProps) {
  return (
    <div className="space-y-6">
      {/* Grid 2 columnas */}
      <div className="grid grid-cols-2 gap-4">
        {/* Nombre Comercial */}
        <div>
          <label className="block text-sm font-medium text-[#1A1A1A] mb-2">
            Nombre Comercial <span className="text-red-500">*</span>
          </label>
          <input
            type="text"
            value={formData.nombreComercial || ''}
            onChange={(e) => onFieldChange('nombreComercial', e.target.value)}
            disabled={isViewMode}
            className={`w-full h-[50px] px-4 border rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] disabled:bg-[#F7F7F7] disabled:text-[#6A6A6A] transition-colors ${
              errors.nombreComercial ? 'border-red-500' : 'border-[#E0E0E0]'
            }`}
            placeholder="Ej: Restaurante El Buen Sabor"
          />
          {errors.nombreComercial && (
            <p className="mt-1 text-sm text-red-500">{errors.nombreComercial}</p>
          )}
        </div>

        {/* NIT */}
        <div>
          <label className="block text-sm font-medium text-[#1A1A1A] mb-2">
            NIT o Cédula <span className="text-red-500">*</span>
          </label>
          <input
            type="text"
            value={formData.nit || ''}
            onChange={(e) => onFieldChange('nit', e.target.value)}
            disabled={isViewMode}
            className={`w-full h-[50px] px-4 border rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] disabled:bg-[#F7F7F7] disabled:text-[#6A6A6A] transition-colors ${
              errors.nit ? 'border-red-500' : 'border-[#E0E0E0]'
            }`}
            placeholder="Ej: 900123456-7"
          />
          {errors.nit && (
            <p className="mt-1 text-sm text-red-500">{errors.nit}</p>
          )}
        </div>

        {/* Representante Legal */}
        <div>
          <label className="block text-sm font-medium text-[#1A1A1A] mb-2">
            Representante Legal
          </label>
          <input
            type="text"
            value={formData.representanteLegal || ''}
            onChange={(e) => onFieldChange('representanteLegal', e.target.value)}
            disabled={isViewMode}
            className="w-full h-[50px] px-4 border border-[#E0E0E0] rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] disabled:bg-[#F7F7F7] disabled:text-[#6A6A6A] transition-colors"
            placeholder="Ej: Juan Pérez González"
          />
        </div>

        {/* Tipo de Establecimiento */}
        <div>
          <label className="block text-sm font-medium text-[#1A1A1A] mb-2">
            Tipo de Establecimiento
          </label>
          <input
            type="text"
            value={formData.tipoEstablecimiento || ''}
            onChange={(e) => onFieldChange('tipoEstablecimiento', e.target.value)}
            disabled={isViewMode}
            className="w-full h-[50px] px-4 border border-[#E0E0E0] rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] disabled:bg-[#F7F7F7] disabled:text-[#6A6A6A] transition-colors"
            placeholder="Ej: Restaurante, Cafetería"
          />
        </div>

        {/* Teléfono */}
        <div>
          <label className="block text-sm font-medium text-[#1A1A1A] mb-2">
            Teléfono <span className="text-red-500">*</span>
          </label>
          <input
            type="tel"
            value={formData.telefono || ''}
            onChange={(e) => onFieldChange('telefono', e.target.value)}
            disabled={isViewMode}
            className={`w-full h-[50px] px-4 border rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] disabled:bg-[#F7F7F7] disabled:text-[#6A6A6A] transition-colors ${
              errors.telefono ? 'border-red-500' : 'border-[#E0E0E0]'
            }`}
            placeholder="Ej: +57 300 123 4567"
          />
          {errors.telefono && (
            <p className="mt-1 text-sm text-red-500">{errors.telefono}</p>
          )}
        </div>

        {/* Email */}
        <div>
          <label className="block text-sm font-medium text-[#1A1A1A] mb-2">
            Correo Electrónico <span className="text-red-500">*</span>
          </label>
          <input
            type="email"
            value={formData.email || ''}
            onChange={(e) => onFieldChange('email', e.target.value)}
            disabled={isViewMode}
            className={`w-full h-[50px] px-4 border rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] disabled:bg-[#F7F7F7] disabled:text-[#6A6A6A] transition-colors ${
              errors.email ? 'border-red-500' : 'border-[#E0E0E0]'
            }`}
            placeholder="Ej: contacto@empresa.com"
          />
          {errors.email && (
            <p className="mt-1 text-sm text-red-500">{errors.email}</p>
          )}
        </div>

        {/* Departamento */}
        <div>
          <label className="block text-sm font-medium text-[#1A1A1A] mb-2">
            Departamento
          </label>
          <input
            type="text"
            value={formData.departamento || ''}
            onChange={(e) => onFieldChange('departamento', e.target.value)}
            disabled={isViewMode}
            className="w-full h-[50px] px-4 border border-[#E0E0E0] rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] disabled:bg-[#F7F7F7] disabled:text-[#6A6A6A] transition-colors"
            placeholder="Ej: Antioquia"
          />
        </div>

        {/* Ciudad */}
        <div>
          <label className="block text-sm font-medium text-[#1A1A1A] mb-2">
            Ciudad
          </label>
          <input
            type="text"
            value={formData.ciudad || ''}
            onChange={(e) => onFieldChange('ciudad', e.target.value)}
            disabled={isViewMode}
            className="w-full h-[50px] px-4 border border-[#E0E0E0] rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] disabled:bg-[#F7F7F7] disabled:text-[#6A6A6A] transition-colors"
            placeholder="Ej: Medellín"
          />
        </div>

        {/* Barrio */}
        <div>
          <label className="block text-sm font-medium text-[#1A1A1A] mb-2">
            Barrio
          </label>
          <input
            type="text"
            value={formData.barrio || ''}
            onChange={(e) => onFieldChange('barrio', e.target.value)}
            disabled={isViewMode}
            className="w-full h-[50px] px-4 border border-[#E0E0E0] rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] disabled:bg-[#F7F7F7] disabled:text-[#6A6A6A] transition-colors"
            placeholder="Ej: El Poblado"
          />
        </div>

        {/* Dirección */}
        <div>
          <label className="block text-sm font-medium text-[#1A1A1A] mb-2">
            Dirección Principal
          </label>
          <input
            type="text"
            value={formData.direccion || ''}
            onChange={(e) => onFieldChange('direccion', e.target.value)}
            disabled={isViewMode}
            className="w-full h-[50px] px-4 border border-[#E0E0E0] rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] disabled:bg-[#F7F7F7] disabled:text-[#6A6A6A] transition-colors"
            placeholder="Ej: Calle 10 # 40-20"
          />
        </div>

        {/* Perfil - Colspan-2 */}
        <div className="col-span-2">
          <label className="block text-sm font-medium text-[#1A1A1A] mb-2">
            Perfil Asignado
          </label>
          <select
            value={formData.perfil || ''}
            onChange={(e) => onFieldChange('perfil', e.target.value)}
            disabled={isViewMode}
            className="w-full h-[50px] px-4 border border-[#E0E0E0] rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] disabled:bg-[#F7F7F7] disabled:text-[#6A6A6A] transition-colors"
          >
            <option value="">Seleccionar perfil...</option>
            {perfiles.map((perfil) => (
              <option key={perfil.id} value={perfil.nombre}>
                {perfil.nombre}
              </option>
            ))}
          </select>
        </div>
      </div>

      {/* Sección de Documentos */}
      <div>
        <label className="block text-sm font-medium text-[#1A1A1A] mb-3">
          Documentos
        </label>
        
        {formData.documentos && formData.documentos.length > 0 ? (
          <div className="space-y-2">
            {formData.documentos.map((doc) => (
              <DocumentItem key={doc.id} documento={doc} />
            ))}
          </div>
        ) : (
          <div className="p-4 border border-[#E0E0E0] rounded-xl bg-[#F7F7F7]">
            <p className="text-sm text-[#6A6A6A] text-center">
              No hay documentos cargados
            </p>
          </div>
        )}
      </div>
    </div>
  );
}

/**
 * Tab: Sucursales
 * TODO: Implementar en Task #5
 */
function SucursalesTab({ formData, isViewMode, onFieldChange }: TabProps) {
  return (
    <div className="space-y-4">
      <p className="text-[#6A6A6A] text-sm">
        🏢 Tab de Sucursales - En construcción (Task #5)
      </p>
      <div className="bg-[#F7F7F7] rounded-lg p-4">
        <pre className="text-xs">{JSON.stringify(formData.sucursales, null, 2)}</pre>
      </div>
    </div>
  );
}

interface BancariaTabProps extends TabProps {
  errors: Record<string, string>;
}

/**
 * Tab: Información Bancaria
 * TODO: Implementar en Task #6
 */
function BancariaTab({ formData, isViewMode, errors, onFieldChange }: BancariaTabProps) {
  return (
    <div className="space-y-4">
      <p className="text-[#6A6A6A] text-sm">
        💳 Tab de Información Bancaria - En construcción (Task #6)
      </p>
      <div className="bg-[#F7F7F7] rounded-lg p-4">
        <pre className="text-xs">{JSON.stringify(formData.informacionBancaria, null, 2)}</pre>
      </div>
    </div>
  );
}

/**
 * Tab: Legal
 * TODO: Implementar en Task #7
 */
function LegalTab({ formData, isViewMode, onFieldChange }: TabProps) {
  return (
    <div className="space-y-4">
      <p className="text-[#6A6A6A] text-sm">
        ⚖️ Tab Legal - En construcción (Task #7)
      </p>
      <div className="bg-[#F7F7F7] rounded-lg p-4">
        <pre className="text-xs">{JSON.stringify(formData.legal, null, 2)}</pre>
      </div>
    </div>
  );
}

// ============================================
// Helper Components
// ============================================

/**
 * Componente para mostrar un documento individual
 */
interface DocumentItemProps {
  documento: DocumentoProveedor;
}

function DocumentItem({ documento }: DocumentItemProps) {
  const handleDownload = () => {
    // TODO: Implementar descarga real
    console.log('Descargar documento:', documento);
    
    // Abrir en nueva pestaña por ahora
    if (documento.url && documento.url !== '#') {
      window.open(documento.url, '_blank');
    }
  };

  // Mapeo de tipos de documento a labels legibles
  const tipoLabels: Record<DocumentoProveedor['tipo'], string> = {
    cedula_ciudadania: 'Cédula de Ciudadanía',
    cedula_extranjeria: 'Cédula de Extranjería',
    pasaporte: 'Pasaporte',
    camara_comercio: 'Cámara de Comercio',
  };

  return (
    <div className="flex items-center justify-between p-3 border border-[#E0E0E0] rounded-xl hover:bg-[#F7F7F7] transition-colors">
      <div className="flex items-center gap-3">
        <div className="flex-shrink-0 w-10 h-10 bg-[#4B236A]/10 rounded-lg flex items-center justify-center">
          <FileText className="w-5 h-5 text-[#4B236A]" />
        </div>
        <div>
          <p className="text-sm font-medium text-[#1A1A1A]">
            {tipoLabels[documento.tipo] || documento.nombre}
          </p>
          {documento.fechaSubida && (
            <p className="text-xs text-[#6A6A6A]">
              Subido: {new Date(documento.fechaSubida).toLocaleDateString('es-CO')}
            </p>
          )}
        </div>
      </div>
      <button
        onClick={handleDownload}
        className="flex items-center gap-2 px-3 py-2 text-[#4B236A] hover:bg-[#4B236A] hover:text-white rounded-lg transition-colors"
        aria-label={`Descargar ${tipoLabels[documento.tipo]}`}
      >
        <Download className="w-4 h-4" />
        <span className="text-sm">Descargar</span>
      </button>
    </div>
  );
}

