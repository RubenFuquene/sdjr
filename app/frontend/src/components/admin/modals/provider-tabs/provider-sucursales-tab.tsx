/**
 * Tab: Sucursales del Proveedor
 * 
 * Responsabilidades:
 * - Listado de sucursales del proveedor
 * - Card individual por sucursal con información completa
 * - Galería de fotos por sucursal
 */

'use client';

import { MapPin } from 'lucide-react';
import type { Proveedor, Sucursal } from '@/types/admin';

// ============================================
// Props Interface
// ============================================

interface ProviderSucursalesTabProps {
  formData: Proveedor;
  isViewMode: boolean;
  onFieldChange: (field: keyof Proveedor, value: unknown) => void;
}

// ============================================
// Component
// ============================================

export function ProviderSucursalesTab({
  formData,
  isViewMode,
  onFieldChange,
}: ProviderSucursalesTabProps) {
  const sucursales = formData.sucursales || [];
  const totalSucursales = sucursales.length;

  return (
    <div className="space-y-4">
      {/* Header con contador */}
      <div className="flex items-center justify-between">
        <p className="text-sm text-[#6A6A6A]">
          {totalSucursales > 0
            ? `Total de sucursales: ${totalSucursales}`
            : 'No hay sucursales registradas'}
        </p>
      </div>

      {/* Lista de Sucursales */}
      {totalSucursales > 0 ? (
        <div className="space-y-4">
          {sucursales.map((sucursal, idx) => (
            <SucursalCard key={sucursal.id || idx} sucursal={sucursal} />
          ))}
        </div>
      ) : (
        <div className="p-8 border border-[#E0E0E0] rounded-xl bg-[#F7F7F7] text-center">
          <MapPin className="w-12 h-12 text-[#6A6A6A] mx-auto mb-3" />
          <p className="text-[#6A6A6A]">No hay sucursales registradas</p>
          <p className="text-sm text-[#6A6A6A] mt-1">
            Las sucursales se mostrarán aquí cuando estén disponibles
          </p>
        </div>
      )}
    </div>
  );
}

// ============================================
// Helper Components
// ============================================

/**
 * Componente para mostrar una sucursal individual
 */
interface SucursalCardProps {
  sucursal: Sucursal;
}

function SucursalCard({ sucursal }: SucursalCardProps) {
  return (
    <div className="p-5 border border-[#E0E0E0] rounded-xl hover:shadow-md transition-shadow">
      {/* Header con nombre de sede */}
      <div className="flex items-center gap-3 mb-4">
        <div className="flex-shrink-0 w-10 h-10 bg-[#4B236A]/10 rounded-lg flex items-center justify-center">
          <MapPin className="w-5 h-5 text-[#4B236A]" />
        </div>
        <h3 className="text-lg font-semibold text-[#1A1A1A]">
          {sucursal.nombreSede || 'Sede sin nombre'}
        </h3>
      </div>

      {/* Grid de información */}
      <div className="grid grid-cols-2 gap-3 mb-4">
        {/* Ubicación */}
        {(sucursal.departamento || sucursal.ciudad) && (
          <div>
            <p className="text-xs font-medium text-[#6A6A6A] mb-1">Ubicación</p>
            <p className="text-sm text-[#1A1A1A]">
              {[sucursal.departamento, sucursal.ciudad, sucursal.barrio]
                .filter(Boolean)
                .join(', ') || 'No especificada'}
            </p>
          </div>
        )}

        {/* Dirección */}
        {sucursal.direccion && (
          <div>
            <p className="text-xs font-medium text-[#6A6A6A] mb-1">Dirección</p>
            <p className="text-sm text-[#1A1A1A]">{sucursal.direccion}</p>
          </div>
        )}

        {/* Contacto */}
        {sucursal.nombreContacto && (
          <div>
            <p className="text-xs font-medium text-[#6A6A6A] mb-1">Contacto</p>
            <p className="text-sm text-[#1A1A1A]">{sucursal.nombreContacto}</p>
          </div>
        )}

        {/* Teléfono */}
        {sucursal.numeroContacto && (
          <div>
            <p className="text-xs font-medium text-[#6A6A6A] mb-1">Teléfono</p>
            <p className="text-sm text-[#1A1A1A]">{sucursal.numeroContacto}</p>
          </div>
        )}

        {/* Horario */}
        {sucursal.horario && (
          <div>
            <p className="text-xs font-medium text-[#6A6A6A] mb-1">Horario</p>
            <p className="text-sm text-[#1A1A1A]">{sucursal.horario}</p>
          </div>
        )}

        {/* Descripción */}
        {sucursal.descripcion && (
          <div className="col-span-2">
            <p className="text-xs font-medium text-[#6A6A6A] mb-1">Descripción</p>
            <p className="text-sm text-[#1A1A1A]">{sucursal.descripcion}</p>
          </div>
        )}
      </div>

      {/* Galería de fotos */}
      {sucursal.fotos && sucursal.fotos.length > 0 && (
        <div>
          <p className="text-xs font-medium text-[#6A6A6A] mb-2">
            Fotos ({sucursal.fotos.length})
          </p>
          <div className="flex gap-2 overflow-x-auto pb-2">
            {sucursal.fotos.map((foto, fotoIdx) => (
              <img
                key={fotoIdx}
                src={foto}
                alt={`Foto ${fotoIdx + 1} de ${sucursal.nombreSede}`}
                className="w-20 h-20 rounded-lg object-cover border border-[#E0E0E0] flex-shrink-0 hover:scale-105 transition-transform cursor-pointer"
                onClick={() => window.open(foto, '_blank')}
              />
            ))}
          </div>
        </div>
      )}
    </div>
  );
}
