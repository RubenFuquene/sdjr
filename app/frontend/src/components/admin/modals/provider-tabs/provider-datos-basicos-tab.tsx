/**
 * Tab: Datos Básicos del Proveedor
 * 
 * Responsabilidades:
 * - Formulario de 10 campos principales del proveedor
 * - Sección de documentos del proveedor
 * - Validación de campos obligatorios
 */

'use client';

import { FileText, Download } from 'lucide-react';
import type { Proveedor, Perfil, DocumentoProveedor } from '@/types/admin';

// ============================================
// Props Interface
// ============================================

interface ProviderDatosBasicosTabProps {
  formData: Proveedor;
  perfiles: Perfil[];
  isViewMode: boolean;
  errors: Record<string, string>;
  onFieldChange: (field: keyof Proveedor, value: unknown) => void;
}

// ============================================
// Component
// ============================================

export function ProviderDatosBasicosTab({
  formData,
  perfiles,
  isViewMode,
  errors,
  onFieldChange,
}: ProviderDatosBasicosTabProps) {
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
