/**
 * Modal principal para creación/edición de roles
 * Implementa formulario base del TODO #1
 */

'use client';

import { useState, useEffect } from 'react';
import { RoleCreationModalProps, RoleFormData } from '../../../types/role-form-types';
import { usePermissions } from '../../../hooks/use-permissions';
import { PermissionTreeView } from './permission-tree-view';

export function RoleCreationModal({
  isOpen,
  mode,
  role,
  onClose,
  onSave
}: RoleCreationModalProps) {
  const [formData, setFormData] = useState<RoleFormData>({
    name: '',
    description: '',
    selectedPermissions: []
  });
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [validationErrors, setValidationErrors] = useState<Record<string, string>>({});
  
  const { permissionTree, loading: permissionsLoading } = usePermissions();

  // Cargar datos del rol en modo edición
  useEffect(() => {
    if (role && (mode === 'edit' || mode === 'view')) {
      setFormData({
        name: role.name,
        description: role.description,
        selectedPermissions: role.permissions
      });
    } else {
      setFormData({
        name: '',
        description: '',
        selectedPermissions: []
      });
    }
    setValidationErrors({});
  }, [role, mode, isOpen]);

  // Validación del formulario
  const validateForm = (): boolean => {
    const errors: Record<string, string> = {};

    if (!formData.name.trim()) {
      errors.name = 'El nombre es obligatorio';
    }

    if (!formData.description.trim()) {
      errors.description = 'La descripción es obligatoria';
    }

    if (formData.selectedPermissions.length === 0) {
      errors.permissions = 'Debe seleccionar al menos un permiso';
    }

    setValidationErrors(errors);
    return Object.keys(errors).length === 0;
  };

  // Submit del formulario
  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault();
    
    if (!validateForm()) return;

    try {
      setIsSubmitting(true);
      
      // Enviar permisos directamente (backend ya usa formato 4 niveles)
      await onSave({
        name: formData.name.trim(),
        description: formData.description.trim(),
        permissions: formData.selectedPermissions
      });
      
      onClose();
    } catch (error) {
      console.error('Error al guardar rol:', error);
      setValidationErrors({ 
        submit: error instanceof Error ? error.message : 'Error al guardar el rol' 
      });
    } finally {
      setIsSubmitting(false);
    }
  };

  // Cerrar modal
  const handleClose = () => {
    if (!isSubmitting) {
      onClose();
    }
  };

  // Toggle permiso individual (placeholder)
  const handlePermissionToggle = (permissionName: string) => {
    setFormData(prev => ({
      ...prev,
      selectedPermissions: prev.selectedPermissions.includes(permissionName)
        ? prev.selectedPermissions.filter(p => p !== permissionName)
        : [...prev.selectedPermissions, permissionName]
    }));
  };

  if (!isOpen) return null;

  return (
    <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
      <div className="max-w-4xl w-full bg-white rounded-[18px] shadow-2xl max-h-[90vh] overflow-y-auto">
        {/* Header */}
        <div className="p-6 border-b border-[#E0E0E0]">
          <div className="flex justify-between items-center">
            <h2 className="text-2xl font-semibold text-[#1A1A1A]">
              {mode === 'create' && 'Crear Rol'}
              {mode === 'edit' && 'Editar Rol'}
              {mode === 'view' && 'Ver Rol'}
            </h2>
            <button
              onClick={handleClose}
              disabled={isSubmitting}
              className="w-8 h-8 flex items-center justify-center text-[#6A6A6A] hover:text-[#1A1A1A] transition-colors"
            >
              ✕
            </button>
          </div>
        </div>

        <form onSubmit={handleSubmit} className="p-6 space-y-6">
          {/* Campos Básicos */}
          <div className="space-y-4">
            <h3 className="text-lg font-medium text-[#1A1A1A]">
              Información Básica
            </h3>
            
            {/* Nombre */}
            <div>
              <label className="block text-sm font-medium text-[#1A1A1A] mb-2">
                Nombre del Rol *
              </label>
              <input
                type="text"
                value={formData.name}
                onChange={(e) => setFormData(prev => ({ ...prev, name: e.target.value }))}
                disabled={mode === 'view' || isSubmitting}
                className="w-full h-[50px] px-4 border border-[#E0E0E0] rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] transition-all"
                placeholder="Ej: Administrador General"
              />
              {validationErrors.name && (
                <p className="mt-1 text-sm text-red-500">{validationErrors.name}</p>
              )}
            </div>

            {/* Descripción */}
            <div>
              <label className="block text-sm font-medium text-[#1A1A1A] mb-2">
                Descripción *
              </label>
              <textarea
                value={formData.description}
                onChange={(e) => setFormData(prev => ({ ...prev, description: e.target.value }))}
                disabled={mode === 'view' || isSubmitting}
                rows={3}
                className="w-full px-4 py-3 border border-[#E0E0E0] rounded-[14px] focus:outline-none focus:ring-2 focus:ring-[#4B236A] transition-all resize-none"
                placeholder="Describe las responsabilidades de este rol..."
              />
              {validationErrors.description && (
                <p className="mt-1 text-sm text-red-500">{validationErrors.description}</p>
              )}
            </div>
          </div>

          {/* Sección de Permisos */}
          <div className="space-y-4">
            <h3 className="text-lg font-medium text-[#1A1A1A]">
              Permisos
            </h3>
            
            {permissionsLoading ? (
              <div className="flex items-center justify-center py-8">
                <div className="animate-spin rounded-full h-8 w-8 border-b-2 border-[#4B236A]"></div>
                <span className="ml-3 text-[#6A6A6A]">Cargando permisos...</span>
              </div>
            ) : (
              <PermissionTreeView
                permissionTree={permissionTree}
                selectedPermissions={formData.selectedPermissions}
                onPermissionToggle={handlePermissionToggle}
                disabled={mode === 'view' || isSubmitting}
              />
            )}
            
            {validationErrors.permissions && (
              <p className="text-sm text-red-500">{validationErrors.permissions}</p>
            )}
          </div>

          {/* Error general */}
          {validationErrors.submit && (
            <div className="p-4 bg-red-50 border border-red-200 rounded-[14px]">
              <p className="text-sm text-red-600">{validationErrors.submit}</p>
            </div>
          )}

          {/* Actions */}
          <div className="flex justify-end space-x-4 pt-4 border-t border-[#E0E0E0]">
            {mode === 'view' && (
              <button
                type="button"
                onClick={handleClose}
                disabled={isSubmitting}
                className="px-6 h-[52px] border border-[#E0E0E0] text-[#6A6A6A] rounded-xl hover:bg-[#F7F7F7] transition-colors disabled:opacity-50"
              >
                Cerrar
              </button>
            )}
            {/* Botones adicionales (solo en modo edición/creación) */}
            {mode !== 'view' && (
              <>
                <button
                  type="button"
                  onClick={handleClose}
                  disabled={isSubmitting}
                  className="px-6 h-[52px] border border-[#E0E0E0] text-[#6A6A6A] rounded-xl hover:bg-[#F7F7F7] transition-colors disabled:opacity-50"
                >
                  Cancelar
                </button>
                <button
                  type="submit"
                  disabled={isSubmitting || permissionsLoading}
                  className="px-6 h-[52px] bg-[#4B236A] text-white rounded-xl hover:bg-[#5D2B7D] transition-all shadow-lg hover:shadow-xl disabled:opacity-50"
                >
                  {isSubmitting ? 'Guardando...' : (mode === 'create' ? 'Crear Rol' : 'Guardar Cambios')}
                </button>
              </>
            )}
          </div>
        </form>
      </div>
    </div>
  );
}