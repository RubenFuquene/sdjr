/**
 * Modal de Creación de Administradores
 * 
 * Wrapper sobre UserVisualizationModal que:
 * - Preselecciona un rol administrativo (rol que contenga 'admin')
 * - Restringe selector de roles a únicamente roles administrativos
 * - Filtra dinámicamente roles usando wildcard 'admin'
 * - Valida que el rol sea administrativo antes de guardar
 * - Mapea respuesta a tipo Administrador
 * 
 * Patrón: Composition wrapper manteniendo SRP
 */

'use client';

import { useMemo } from 'react';
import type { Usuario } from '@/types/admin';
import type { CreateUserPayload } from '@/types/user';
import { UserVisualizationModal } from './user-visualization-modal';

// ============================================
// Constants
// ============================================

const DEFAULT_ADMIN_ROLE = 'admin';
const ADMIN_ROLE_WILDCARD = 'admin';

// ============================================
// Props Interface
// ============================================

interface AdministratorCreationModalProps {
  /** Controla si el modal está abierto */
  isOpen: boolean;
  /** Roles disponibles del sistema (se filtrarán a roles que contengan 'admin') */
  availableRoles: string[];
  /** Callback al cerrar el modal */
  onClose: () => void;
  /** Callback al guardar (recibe el payload para crear usuario) */
  onSave: (payload: CreateUserPayload) => Promise<void>;
  /** Rol por defecto (debe contener 'admin' en su nombre) */
  defaultRole?: string;
}

// ============================================
// Helper Functions
// ============================================

/**
 * Crea un usuario vacío con rol admin preseleccionado
 */
function createEmptyAdminUser(defaultRole: string): Usuario {
  return {
    id: 0, // Temporal, se asignará por backend
    nombres: '',
    apellidos: '',
    celular: '',
    email: '',
    perfil: defaultRole,
    activo: true,
  };
}

/**
 * Valida que el rol contenga 'admin' en su nombre (case-insensitive)
 * Detecta: admin, superadmin, admin-marketing, system-admin, etc.
 */
function isAdminRole(role: string): boolean {
  return role.toLowerCase().includes(ADMIN_ROLE_WILDCARD);
}

// ============================================
// Component
// ============================================

export function AdministratorCreationModal({
  isOpen,
  availableRoles,
  onClose,
  onSave,
  defaultRole = DEFAULT_ADMIN_ROLE,
}: AdministratorCreationModalProps) {
  // Filtrar roles disponibles a solo roles administrativos
  const adminRoles = useMemo(() => {
    return availableRoles.filter(isAdminRole);
  }, [availableRoles]);

  const emptyUser = isOpen ? createEmptyAdminUser(defaultRole) : null;

  // ============================================
  // Handlers
  // ============================================

  /**
   * Maneja el guardado del nuevo administrador
   * Valida y prepara el payload, luego delega al callback
   */
  const handleSave = async (updatedUsuario: Usuario) => {
    // Validar que el rol siga siendo administrativo
    if (!isAdminRole(updatedUsuario.perfil)) {
      throw new Error(
        `El rol "${updatedUsuario.perfil}" no es válido para administradores. ` +
        `Debe ser un rol administrativo (debe contener 'admin' en su nombre).`
      );
    }

    // Preparar payload para backend
    const payload: CreateUserPayload = {
      name: updatedUsuario.nombres,
      last_name: updatedUsuario.apellidos,
      email: updatedUsuario.email,
      phone: updatedUsuario.celular,
      password: 'TempPassword123!', // TODO: Implementar generación/envío de password temporal
      password_confirmation: 'TempPassword123!',
      status: (updatedUsuario.activo ? '1' : '0') as '1' | '0',
      roles: [updatedUsuario.perfil],
    };

    // Delegar guardado al callback (view usa hook)
    await onSave(payload);
  };

  /**
   * Cierra el modal y limpia estados
   */
  const handleClose = () => {
    onClose();
  };

  // ============================================
  // Render
  // ============================================

  // Si no hay usuario vacío, no renderizar
  if (!emptyUser) return null;

  return (
    <UserVisualizationModal
      key={`admin-create-${isOpen ? 'open' : 'closed'}`}
      isOpen={isOpen}
      mode="edit"
      usuario={emptyUser}
      roles={adminRoles}
      onClose={handleClose}
      onSave={handleSave}
    />
  );
}
