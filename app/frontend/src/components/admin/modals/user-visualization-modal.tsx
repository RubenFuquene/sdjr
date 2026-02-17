/**
 * Modal de Visualización/Edición de Usuarios
 * 
 * Responsabilidades:
 * - Orquestar la visualización/edición de usuarios
 * - Manejar estado del formulario y validación
 * - Controlar apertura/cierre del modal
 * - Guardar cambios del usuario
 * 
 * Nota: Los componentes de cada tab están en user-tabs/
 * Basado en: ProviderVisualizationModal pattern
 */

'use client';

import { useState } from 'react';
import { X, Check } from 'lucide-react';
import type { Usuario } from '@/types/admin';
import { UserInformacionTab } from './user-tabs';

// ============================================
// Props Interface
// ============================================

interface UserVisualizationModalProps {
  /** Controla si el modal está abierto */
  isOpen: boolean;
  /** Define si el modal es solo lectura (view) o editable (edit) */
  mode: 'view' | 'edit';
  /** Datos del usuario a visualizar/editar */
  usuario: Usuario | null;
  /** Lista de roles disponibles para asignar */
  roles?: string[];
  /** Callback al cerrar el modal */
  onClose: () => void;
  /** Callback al guardar cambios (solo en modo edit) */
  onSave?: (updatedUsuario: Usuario) => void;
}

// ============================================
// Component
// ============================================

export function UserVisualizationModal({
  isOpen,
  mode,
  usuario,
  roles = ['admin', 'provider', 'customer'],
  onClose,
  onSave,
}: UserVisualizationModalProps) {
  // Estado del formulario
  const [formData, setFormData] = useState<Usuario | null>(() =>
    usuario ? { ...usuario } : null
  );

  // Estado de validación
  const [errors, setErrors] = useState<Record<string, string>>({});

  // Si el modal no está abierto, no renderizar
  if (!isOpen || !formData) return null;

  // ============================================
  // Handlers
  // ============================================

  /**
   * Valida los campos obligatorios
   */
  const validateForm = (): boolean => {
    const newErrors: Record<string, string> = {};

    if (!formData.nombres?.trim()) {
      newErrors.nombres = 'Los nombres son requeridos';
    }

    if (!formData.apellidos?.trim()) {
      newErrors.apellidos = 'Los apellidos son requeridos';
    }

    if (!formData.email?.trim()) {
      newErrors.email = 'El email es requerido';
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.email)) {
      newErrors.email = 'El email no es válido';
    }

    if (!formData.celular?.trim()) {
      newErrors.celular = 'El celular es requerido';
    }

    if (!formData.perfil?.trim()) {
      newErrors.perfil = 'El rol es requerido';
    }

    setErrors(newErrors);
    return Object.keys(newErrors).length === 0;
  };

  /**
   * Maneja cambios en los campos del formulario
   */
  const handleFieldChange = (field: keyof Usuario, value: unknown) => {
    setFormData((prev) => {
      if (!prev) return null;
      return {
        ...prev,
        [field]: value,
      };
    });

    // Limpiar error del campo si existe
    if (errors[field]) {
      setErrors((prev) => {
        const newErrors = { ...prev };
        delete newErrors[field];
        return newErrors;
      });
    }
  };

  /**
   * Maneja el guardado de cambios
   * Solo valida y delega al callback onSave
   */
  const handleSave = async () => {
    if (!validateForm()) {
      return;
    }

    if (!formData) return;

    // Delegar guardado al componente padre
    if (onSave) {
      await onSave(formData);
    }
  };

  /**
   * Cierra el modal y limpia estados
   */
  const handleClose = () => {
    setFormData(null);
    setErrors({});
    onClose();
  };

  // ============================================
  // Render
  // ============================================

  const isViewMode = mode === 'view';
  const title = isViewMode ? 'Ver Usuario' : 'Editar Usuario';

  return (
    <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div className="bg-white rounded-[18px] shadow-2xl w-[90%] max-w-2xl max-h-[90vh] overflow-auto">
        {/* ===== Header ===== */}
        <div className="flex items-center justify-between p-8 border-b border-[#E0E0E0]">
          <h2 className="text-2xl font-bold text-[#1A1A1A]">{title}</h2>
          <button
            onClick={handleClose}
            className="text-[#6A6A6A] hover:text-[#1A1A1A] transition-colors"
            aria-label="Cerrar modal"
          >
            <X size={24} />
          </button>
        </div>

        {/* ===== Body ===== */}
        <div className="p-8">
          {/* Formulario */}
          <UserInformacionTab
            formData={formData}
            isViewMode={isViewMode}
            errors={errors}
            onFieldChange={handleFieldChange}
            roles={roles}
          />
        </div>

        {/* ===== Footer ===== */}
        <div className="flex gap-3 justify-end p-8 border-t border-[#E0E0E0] bg-[#F7F7F7]">
          <button
            onClick={handleClose}
            className="px-6 h-[52px] rounded-xl border border-[#E0E0E0] text-[#1A1A1A] font-medium hover:bg-[#E0E0E0] transition-colors"
          >
            {isViewMode ? 'Cerrar' : 'Cancelar'}
          </button>

          {!isViewMode && (
            <button
              onClick={handleSave}
              className="px-6 h-[52px] rounded-xl bg-[#4B236A] text-white font-medium hover:bg-[#5D2B7D] transition-colors flex items-center gap-2"
            >
              <Check size={20} />
              Guardar Cambios
            </button>
          )}
        </div>
      </div>
    </div>
  );
}
