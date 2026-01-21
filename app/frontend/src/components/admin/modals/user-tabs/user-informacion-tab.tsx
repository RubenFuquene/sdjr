/**
 * Tab: Información del Usuario
 * 
 * Responsabilidades:
 * - Formulario de campos principales del usuario
 * - Validación de campos obligatorios
 * - Soporte para view mode (lectura) y edit mode (editable)
 */

'use client';

import type { Usuario } from '@/types/admin';

// ============================================
// Props Interface
// ============================================

interface UserInformacionTabProps {
  /** Datos del usuario a visualizar/editar */
  formData: Usuario;
  /** Si está en modo visualización (solo lectura) */
  isViewMode: boolean;
  /** Errores de validación por campo */
  errors: Record<string, string>;
  /** Callback para cambios de campo */
  onFieldChange: (field: keyof Usuario, value: unknown) => void;
  /** Lista de roles disponibles para el dropdown */
  roles?: string[];
}

// ============================================
// Component
// ============================================

export function UserInformacionTab({
  formData,
  isViewMode,
  errors,
  onFieldChange,
  roles = ['admin', 'provider', 'customer'],
}: UserInformacionTabProps) {
  return (
    <div className="space-y-6">
      {/* Grid 2 columnas para campos principales */}
      <div className="grid grid-cols-2 gap-6">
        {/* Nombres */}
        <div>
          <label className="block text-sm font-medium text-[#1A1A1A] mb-2">
            Nombres <span className="text-red-500">*</span>
          </label>
          <input
            type="text"
            value={formData.nombres || ''}
            onChange={(e) => onFieldChange('nombres', e.target.value)}
            disabled={isViewMode}
            className={`w-full h-[50px] px-4 border rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] disabled:bg-[#F7F7F7] disabled:text-[#6A6A6A] transition-colors ${
              errors.nombres ? 'border-red-500' : 'border-[#E0E0E0]'
            }`}
            placeholder="Ej: Juan"
          />
          {errors.nombres && (
            <p className="mt-1 text-sm text-red-500">{errors.nombres}</p>
          )}
        </div>

        {/* Apellidos */}
        <div>
          <label className="block text-sm font-medium text-[#1A1A1A] mb-2">
            Apellidos <span className="text-red-500">*</span>
          </label>
          <input
            type="text"
            value={formData.apellidos || ''}
            onChange={(e) => onFieldChange('apellidos', e.target.value)}
            disabled={isViewMode}
            className={`w-full h-[50px] px-4 border rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] disabled:bg-[#F7F7F7] disabled:text-[#6A6A6A] transition-colors ${
              errors.apellidos ? 'border-red-500' : 'border-[#E0E0E0]'
            }`}
            placeholder="Ej: García"
          />
          {errors.apellidos && (
            <p className="mt-1 text-sm text-red-500">{errors.apellidos}</p>
          )}
        </div>

        {/* Email */}
        <div>
          <label className="block text-sm font-medium text-[#1A1A1A] mb-2">
            Email <span className="text-red-500">*</span>
          </label>
          <input
            type="email"
            value={formData.email || ''}
            onChange={(e) => onFieldChange('email', e.target.value)}
            disabled={isViewMode}
            className={`w-full h-[50px] px-4 border rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] disabled:bg-[#F7F7F7] disabled:text-[#6A6A6A] transition-colors ${
              errors.email ? 'border-red-500' : 'border-[#E0E0E0]'
            }`}
            placeholder="Ej: juan@example.com"
          />
          {errors.email && (
            <p className="mt-1 text-sm text-red-500">{errors.email}</p>
          )}
        </div>

        {/* Celular */}
        <div>
          <label className="block text-sm font-medium text-[#1A1A1A] mb-2">
            Celular <span className="text-red-500">*</span>
          </label>
          <input
            type="tel"
            value={formData.celular || ''}
            onChange={(e) => onFieldChange('celular', e.target.value)}
            disabled={isViewMode}
            className={`w-full h-[50px] px-4 border rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] disabled:bg-[#F7F7F7] disabled:text-[#6A6A6A] transition-colors ${
              errors.celular ? 'border-red-500' : 'border-[#E0E0E0]'
            }`}
            placeholder="Ej: +57 300 1234567"
          />
          {errors.celular && (
            <p className="mt-1 text-sm text-red-500">{errors.celular}</p>
          )}
        </div>

        {/* Rol/Perfil */}
        <div>
          <label className="block text-sm font-medium text-[#1A1A1A] mb-2">
            Rol/Perfil <span className="text-red-500">*</span>
          </label>
          {isViewMode ? (
            <div className="w-full h-[50px] px-4 border border-[#E0E0E0] rounded-[14px] bg-[#F7F7F7] flex items-center text-[#6A6A6A]">
              {formData.perfil || 'Sin rol asignado'}
            </div>
          ) : (
            <select
              value={formData.perfil || ''}
              onChange={(e) => onFieldChange('perfil', e.target.value)}
              className={`w-full h-[50px] px-4 border rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] transition-colors ${
                errors.perfil ? 'border-red-500' : 'border-[#E0E0E0]'
              }`}
            >
              <option value="">Seleccionar rol...</option>
              {roles.map((role) => (
                <option key={role} value={role}>
                  {role.charAt(0).toUpperCase() + role.slice(1)}
                </option>
              ))}
            </select>
          )}
          {errors.perfil && (
            <p className="mt-1 text-sm text-red-500">{errors.perfil}</p>
          )}
        </div>

        {/* Estado */}
        <div>
          <label className="block text-sm font-medium text-[#1A1A1A] mb-2">
            Estado
          </label>
          <div className="w-full h-[50px] px-4 border border-[#E0E0E0] rounded-[14px] bg-[#F7F7F7] flex items-center">
            <span
              className={`px-3 py-1 rounded-full text-sm font-medium ${
                formData.activo
                  ? 'bg-green-100 text-green-700'
                  : 'bg-red-100 text-red-700'
              }`}
            >
              {formData.activo ? 'Activo' : 'Inactivo'}
            </span>
          </div>
        </div>
      </div>

      {/* Sección de información del sistema (timestamps) */}
      <div className="border-t border-[#E0E0E0] pt-6">
        <h3 className="text-sm font-medium text-[#6A6A6A] mb-4">
          Información del Sistema
        </h3>
        <div className="grid grid-cols-2 gap-6">
          {/* Fecha de creación */}
          <div>
            <label className="block text-sm text-[#6A6A6A] mb-2">
              Fecha de Creación
            </label>
            <div className="text-sm text-[#1A1A1A]">
              {formData.createdAt
                ? new Date(formData.createdAt).toLocaleDateString('es-ES', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                  })
                : 'No disponible'}
            </div>
          </div>

          {/* Última actualización */}
          <div>
            <label className="block text-sm text-[#6A6A6A] mb-2">
              Última Actualización
            </label>
            <div className="text-sm text-[#1A1A1A]">
              {formData.updatedAt
                ? new Date(formData.updatedAt).toLocaleDateString('es-ES', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                  })
                : 'No disponible'}
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
