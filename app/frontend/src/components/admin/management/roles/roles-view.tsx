'use client';

/**
 * Vista Especializada de Roles
 * 
 * Responsabilidad única: Gestionar UI de roles
 * - Renderizar tabla de roles
 * - Manejar modal de creación/edición/vista
 * - Mostrar loading y error states
 * - Delegar lógica de negocio al hook useRoleManagement
 * 
 * Ventajas del refactor:
 * - Auto-contenido y reutilizable
 * - Separa UI de lógica de negocio
 * - Facilita testing
 * - Responsabilidad única
 */

import { useEffect, useState, ReactNode } from 'react';
import { Plus } from 'lucide-react';
import { CreateRoleRequest } from '@/types/role-form-types';
import { Perfil } from '@/types/admin';
import { useRoleManagement, AdaptedRole } from '@/hooks/use-role-management';
import { ProfilesFilters } from '../profiles-filters';
import { RolesTable } from './roles-table';
import { TableLoadingState } from '@/components/admin/shared/loading-state';
import { ErrorState } from '@/components/admin/shared/error-state';
import { RoleCreationModal } from '@/components/admin/modals';

/**
 * Modal state interface
 */
interface ModalState {
  isOpen: boolean;
  mode: 'create' | 'edit' | 'view';
  selectedRole?: AdaptedRole;
}

/**
 * Vista de Roles - Auto-contenida
 * 
 * Estados:
 * - Loading: Tabla vacía con skeleton
 * - Error: Mensaje de error con botón de reintentar
 * - Empty: Mensaje cuando no hay roles
 * - Success: Tabla con roles y acciones
 */
interface RolesViewProps {
  onSetHeaderActions?: (node: ReactNode | null) => void;
}

export function RolesView({ onSetHeaderActions }: RolesViewProps) {
  const roleManagement = useRoleManagement();
  
  // Estado del modal
  const [modalState, setModalState] = useState<ModalState>({
    isOpen: false,
    mode: 'create'
  });

  // Estado de filtros
  const [searchTerm, setSearchTerm] = useState('');
  const [perfilFilter, setPerfilFilter] = useState('todos');

  /**
   * Abre modal de creación
   */
  const handleCreateClick = () => {
    setModalState({
      isOpen: true,
      mode: 'create',
      selectedRole: undefined
    });
  };

  /**
   * Abre modal de vista (solo lectura)
   */
  const handleViewRole = (role: Perfil) => {
    const adaptedRole = roleManagement.adaptProfileToRole(role);
    setModalState({
      isOpen: true,
      mode: 'view',
      selectedRole: adaptedRole
    });
  };

  /**
   * Abre modal de edición
   */
  const handleEditRole = (role: Perfil) => {
    const adaptedRole = roleManagement.adaptProfileToRole(role);
    setModalState({
      isOpen: true,
      mode: 'edit',
      selectedRole: adaptedRole
    });
  };

  /**
   * Cierra el modal
   */
  const handleCloseModal = () => {
    setModalState({
      isOpen: false,
      mode: 'create',
      selectedRole: undefined
    });
  };

  /**
   * Guarda rol (crear o editar)
   */
  const handleSaveRole = async (roleData: CreateRoleRequest) => {
    try {
      if (modalState.mode === 'edit' && modalState.selectedRole) {
        await roleManagement.handleUpdate(modalState.selectedRole.id, roleData);
      } else {
        await roleManagement.handleCreate(roleData);
      }

      // Cerrar modal después de guardar
      handleCloseModal();
    } catch (error) {
      // El hook maneja el error y logging
      // Re-lanzar para que el modal lo maneje
      throw error;
    }
  };

  /**
   * Buscar roles
   */
  const handleSearch = () => {
    console.log('Buscando:', { searchTerm, perfilFilter });
    // TODO: Implementar lógica de filtrado real cuando sea necesario
  };

  /**
   * Cambiar estado de un rol (activo/inactivo)
   */
  const handleToggleRoleStatus = async (roleId: number, currentStatus: boolean) => {
    try {
      await roleManagement.handleToggleRoleStatus(roleId, currentStatus);
    } catch (error) {
      console.error('Error al cambiar estado del rol:', error);
      // El toast/notificación se maneja en el nivel superior si es necesario
    }
  };

  const buttonClass = 'flex items-center gap-2 px-4 h-[52px] bg-[#4B236A] text-white rounded-xl hover:bg-[#5D2B7D] transition shadow-lg hover:shadow-xl';

  // Header Actions: botón "Crear Rol" en el header superior
  useEffect(() => {
    if (!onSetHeaderActions) return;
    const headerButton = (
      <button onClick={handleCreateClick} className={buttonClass}>
        <Plus className="w-5 h-5" />
        Crear Rol
      </button>
    );
    onSetHeaderActions(headerButton);
    return () => {
      onSetHeaderActions(null);
    };
  }, [onSetHeaderActions]);

  return (
    <div className="space-y-6">
      {/* Filtros */}
      <ProfilesFilters
        vista="perfiles"
        searchTerm={searchTerm}
        perfilFilter={perfilFilter}
        perfiles={roleManagement.roles}
        onSearchChange={setSearchTerm}
        onPerfilChange={setPerfilFilter}
        onSearch={handleSearch}
      />

      {/* El botón de crear se inyecta en el header vía onSetHeaderActions */}

      {/* Estados de carga y error */}
      {roleManagement.loading && <TableLoadingState />}
      {roleManagement.error && (
        <ErrorState 
          message={roleManagement.error} 
          onRetry={roleManagement.refresh}
        />
      )}

      {/* Rol vacío */}
      {!roleManagement.loading && !roleManagement.error && roleManagement.roles.length === 0 && (
        <div className="text-center py-12 text-[#6A6A6A]">
          No se encontraron perfiles
        </div>
      )}

      {/* Tabla de roles */}
      {!roleManagement.loading && !roleManagement.error && roleManagement.roles.length > 0 && (
        <RolesTable
          data={roleManagement.roles}
          onView={handleViewRole}
          onEdit={handleEditRole}
          onToggle={handleToggleRoleStatus}
        />
      )}

      {/* Modal de creación/edición/vista */}
      <RoleCreationModal
        isOpen={modalState.isOpen}
        mode={modalState.mode}
        role={modalState.selectedRole}
        onClose={handleCloseModal}
        onSave={handleSaveRole}
      />
    </div>
  );
}
