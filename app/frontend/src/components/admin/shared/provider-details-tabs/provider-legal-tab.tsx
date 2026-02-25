/**
 * Tab: Información Legal del Proveedor
 * 
 * Responsabilidades:
 * - Mostrar aceptación de términos y condiciones
 * - Fecha de aceptación (read-only en MVP)
 * 
 * Nota: En MVP este tab es solo lectura. La aceptación de términos
 * se realiza durante el registro del proveedor.
 */

'use client';

import { CheckCircle2, FileText, Calendar } from 'lucide-react';
import type { Proveedor } from '@/types/admin';

// ============================================
// Props Interface
// ============================================

interface ProviderLegalTabProps {
  formData: Proveedor;
}

// ============================================
// Component
// ============================================

export function ProviderLegalTab({
  formData,
}: ProviderLegalTabProps) {
  const legal = formData.legal || {
    aceptoTerminos: false,
    fechaAceptacion: '',
  };

  /**
   * Formatea fecha ISO a formato legible
   */
  const formatFecha = (isoDate: string): string => {
    if (!isoDate) return 'No disponible';
    
    try {
      const date = new Date(isoDate);
      return date.toLocaleDateString('es-CO', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
      });
    } catch {
      return 'Fecha inválida';
    }
  };

  return (
    <div className="space-y-6">
      {/* Descripción informativa */}
      <div className="p-4 bg-[#DDE8BB]/30 border border-[#C8D86D] rounded-xl">
        <p className="text-sm text-[#1A1A1A]">
          ⚖️ <strong>Información legal:</strong> Esta sección muestra el estado de aceptación de los términos y condiciones del proveedor.
          La aceptación se realiza durante el proceso de registro.
        </p>
      </div>

      {/* Card principal con estado de términos */}
      <div className="p-6 border-2 border-[#E0E0E0] rounded-xl bg-white">
        {/* Estado de aceptación */}
        <div className="flex items-start gap-4 mb-6">
          <div className={`flex-shrink-0 w-12 h-12 rounded-full flex items-center justify-center ${
            legal.aceptoTerminos 
              ? 'bg-green-100' 
              : 'bg-red-100'
          }`}>
            {legal.aceptoTerminos ? (
              <CheckCircle2 className="w-6 h-6 text-green-600" />
            ) : (
              <FileText className="w-6 h-6 text-red-600" />
            )}
          </div>
          
          <div className="flex-1">
            <h3 className="text-lg font-semibold text-[#1A1A1A] mb-2">
              Términos y Condiciones
            </h3>
            
            <div className="flex items-center gap-2 mb-3">
              <div className={`px-3 py-1 rounded-full text-sm font-medium ${
                legal.aceptoTerminos
                  ? 'bg-green-100 text-green-700'
                  : 'bg-red-100 text-red-700'
              }`}>
                {legal.aceptoTerminos ? '✓ Aceptados' : '✗ No aceptados'}
              </div>
            </div>

            <p className="text-sm text-[#6A6A6A]">
              {legal.aceptoTerminos 
                ? 'El proveedor ha aceptado los términos y condiciones de uso de la plataforma.'
                : 'El proveedor aún no ha aceptado los términos y condiciones.'}
            </p>
          </div>
        </div>

        {/* Fecha de aceptación */}
        {legal.aceptoTerminos && legal.fechaAceptacion && (
          <div className="pt-6 border-t border-[#E0E0E0]">
            <div className="flex items-center gap-3">
              <div className="flex-shrink-0 w-10 h-10 bg-[#4B236A]/10 rounded-lg flex items-center justify-center">
                <Calendar className="w-5 h-5 text-[#4B236A]" />
              </div>
              <div>
                <p className="text-xs font-medium text-[#6A6A6A] mb-1">
                  Fecha de Aceptación
                </p>
                <p className="text-sm font-medium text-[#1A1A1A]">
                  {formatFecha(legal.fechaAceptacion)}
                </p>
              </div>
            </div>
          </div>
        )}
      </div>

      {/* Sección de documentos legales */}
      <div>
        <h4 className="text-sm font-semibold text-[#1A1A1A] mb-3">
          Documentos Disponibles
        </h4>
        
        <div className="space-y-2">
          <DocumentoLegalItem
            titulo="Términos y Condiciones"
            descripcion="Condiciones generales de uso de la plataforma"
            url="/legal/terminos-y-condiciones"
          />
          <DocumentoLegalItem
            titulo="Política de Privacidad"
            descripcion="Tratamiento de datos personales"
            url="/legal/politica-privacidad"
          />
          <DocumentoLegalItem
            titulo="Contrato de Prestación de Servicios"
            descripcion="Acuerdo comercial entre el proveedor y la plataforma"
            url="/legal/contrato-servicios"
          />
        </div>
      </div>

      {/* Nota informativa */}
      <div className="p-4 bg-[#F7F7F7] border border-[#E0E0E0] rounded-xl">
        <p className="text-xs text-[#6A6A6A]">
          📄 <strong>Nota:</strong> Los documentos legales están disponibles para consulta en cualquier momento.
          Si necesitas realizar cambios en el estado de aceptación, contacta al administrador del sistema.
        </p>
      </div>
    </div>
  );
}

// ============================================
// Helper Components
// ============================================

/**
 * Item de documento legal con link
 */
interface DocumentoLegalItemProps {
  titulo: string;
  descripcion: string;
  url: string;
}

function DocumentoLegalItem({ titulo, descripcion, url }: DocumentoLegalItemProps) {
  const handleClick = () => {
    // TODO: Implementar apertura de documento
    console.log('Abrir documento:', url);
    // Por ahora, abrir en nueva pestaña (placeholder)
    window.open(url, '_blank');
  };

  return (
    <button
      onClick={handleClick}
      className="w-full flex items-center justify-between p-3 border border-[#E0E0E0] rounded-xl hover:bg-[#F7F7F7] hover:border-[#4B236A] transition-all group"
    >
      <div className="flex items-center gap-3 text-left">
        <div className="flex-shrink-0 w-10 h-10 bg-[#4B236A]/10 rounded-lg flex items-center justify-center group-hover:bg-[#4B236A] transition-colors">
          <FileText className="w-5 h-5 text-[#4B236A] group-hover:text-white transition-colors" />
        </div>
        <div>
          <p className="text-sm font-medium text-[#1A1A1A] group-hover:text-[#4B236A] transition-colors">
            {titulo}
          </p>
          <p className="text-xs text-[#6A6A6A]">
            {descripcion}
          </p>
        </div>
      </div>
      <span className="text-sm text-[#4B236A] font-medium group-hover:translate-x-1 transition-transform">
        Ver →
      </span>
    </button>
  );
}
