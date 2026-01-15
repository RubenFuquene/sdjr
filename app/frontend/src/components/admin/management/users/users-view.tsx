'use client';

/**
 * Vista Especializada de Usuarios
 * 
 * Responsabilidad única: Gestionar UI de usuarios
 * - Renderizar tabla de usuarios
 * - Manejar modales (creación/edición/vista)
 * - Mostrar loading y error states
 * - Delegar datos al componente presentacional UsersTable
 * 
 * Patrón: Layout State Composition (similar a RolesView y ProvidersView)
 */

import { useState, useCallback, ReactNode, useMemo, useEffect } from 'react';
import { Plus } from 'lucide-react';
import { Usuario } from '@/types/admin';
import { useUserManagement } from '@/hooks/use-user-management';
import { UsersTable } from './users-table';
import { ProfilesFilters } from '../profiles-filters';
import { TableLoadingState } from '@/components/admin/shared/loading-state';
import { ErrorState } from '@/components/admin/shared/error-state';
import { ConfirmationDialog } from '@/components/admin/shared/confirmation-dialog';

/**
 * Props de la vista
 */
interface UsersViewProps {
  onSetHeaderActions?: (node: ReactNode | null) => void;
}

/**
 * Vista de Usuarios - Auto-contenida
 * 
 * Estados:
 * - Loading: Tabla vacía con skeleton
 * - Error: Mensaje de error con botón de reintentar
 * - Empty: Mensaje cuando no hay usuarios
 * - Success: Tabla con usuarios y acciones
 */
export function UsersView({ 
  onSetHeaderActions,
}: UsersViewProps) {
  // Hook de gestión de usuarios
  const userManagement = useUserManagement();
  const { usuarios, loading, error, handleSearch, handleToggle, handleDelete, handleRetry } = userManagement;

  // Estado del confirmation dialog para eliminar
  const [deleteDialog, setDeleteDialog] = useState<{
    isOpen: boolean;
    user: Usuario | null;
  }>({ isOpen: false, user: null });
  const [isDeleting, setIsDeleting] = useState(false);

  // Estado de filtros locales
  const [searchTerm, setSearchTerm] = useState('');
  const [perfilFilter, setPerfilFilter] = useState('todos');

  // Opciones de perfiles (únicos) derivadas de los usuarios
  const perfiles = useMemo(() => {
    const unique = Array.from(new Set(usuarios.map((u) => u.perfil).filter(Boolean)));
    return unique.map((nombre, idx) => ({ id: idx, nombre }));
  }, [usuarios]);

  /**
   * Abre modal de vista (solo lectura)
   * TODO: Implementar cuando tengamos useUserManagement + modal
   */
  const handleViewUser = useCallback(async (usuario: Usuario) => {
    console.log('Ver usuario:', usuario);
    // TODO: Fetch usuario completo desde API
    // TODO: Abrir modal en modo view
  }, []);

  /**
   * Abre modal de edición
   * TODO: Implementar cuando tengamos useUserManagement + modal
   */
  const handleEditUser = useCallback(async (usuario: Usuario) => {
    console.log('Editar usuario:', usuario);
    // TODO: Fetch usuario completo desde API
    // TODO: Abrir modal en modo edit
  }, []);

  /**
   * Activa/Desactiva usuario
   */
  const handleToggleUser = useCallback(async (usuario: Usuario) => {
    try {
      await handleToggle(usuario.id);
    } catch (error) {
      console.error('Error al cambiar estado del usuario:', error);
    }
  }, [handleToggle]);

  /**
   * Abre el dialog de confirmación para eliminar
   */
  const handleDeleteUser = useCallback((usuario: Usuario) => {
    setDeleteDialog({ isOpen: true, user: usuario });
  }, []);

  /**
   * Confirma y ejecuta la eliminación del usuario
   */
  const handleConfirmDelete = useCallback(async () => {
    if (!deleteDialog.user) return;

    try {
      setIsDeleting(true);
      await handleDelete(deleteDialog.user.id);
      setDeleteDialog({ isOpen: false, user: null });
    } catch (error) {
      console.error('Error al eliminar usuario:', error);
    } finally {
      setIsDeleting(false);
    }
  }, [deleteDialog.user, handleDelete]);

  /**
   * Cancela la eliminación
   */
  const handleCancelDelete = useCallback(() => {
    setDeleteDialog({ isOpen: false, user: null });
  }, []);

  /**
   * Busca usuarios con los filtros actuales
   */
  const handleSearchClick = useCallback(async () => {
    try {
      await handleSearch({
        search: searchTerm || undefined,
        role: perfilFilter !== 'todos' ? perfilFilter : undefined,
      });
    } catch (error) {
      console.error('Error al buscar usuarios:', error);
    }
  }, [searchTerm, perfilFilter, handleSearch]);

  /**
   * Crea un nuevo usuario
   * TODO: Implementar modal de creación
   */
  const handleCreateClick = useCallback(() => {
    console.log('Abrir modal de creación de usuario');
    // TODO: Implementar modal de creación
  }, []);

  /**
   * Header Actions: Botón "Crear Usuario"
   */
  useEffect(() => {
    if (!onSetHeaderActions) return;

    const buttonClass = 'flex items-center gap-2 px-4 h-[52px] bg-[#4B236A] text-white rounded-xl hover:bg-[#5D2B7D] transition shadow-lg hover:shadow-xl';
    const button = (
      <button onClick={handleCreateClick} className={buttonClass}>
        <Plus size={20} />
        <span>Crear Usuario</span>
      </button>
    );

    onSetHeaderActions(button);

    return () => onSetHeaderActions(null);
  }, [onSetHeaderActions, handleCreateClick]);

  // Estados condicionales
  if (error) {
    return <ErrorState message={error} onRetry={handleRetry} />;
  }

  if (loading) {
    return <TableLoadingState />;
  }

  if (usuarios.length === 0) {
    return (
      <div className="flex flex-col items-center justify-center py-12">
        <h3 className="text-lg font-medium text-[#1A1A1A] mb-2">No hay usuarios</h3>
        <p className="text-sm text-[#6A6A6A] mb-6">No hay usuarios registrados en el sistema</p>
      </div>
    );
  }

  return (
    <>
      <div className="space-y-6">
        {/* Filtros */}
        <ProfilesFilters 
          vista="usuarios"
          searchTerm={searchTerm}
          perfilFilter={perfilFilter}
          perfiles={perfiles}
          onSearchChange={setSearchTerm}
          onPerfilChange={setPerfilFilter}
          onSearch={handleSearchClick}
        />

        {/* Tabla de Usuarios */}
        <UsersTable
          data={usuarios}
          onView={handleViewUser}
          onEdit={handleEditUser}
          onToggle={handleToggleUser}
          onDelete={handleDeleteUser}
        />
      </div>

      {/* Dialog de Confirmación de Eliminación */}
      <ConfirmationDialog
        isOpen={deleteDialog.isOpen}
        title="¿Eliminar usuario?"
        description={
          deleteDialog.user ? (
            <>
              Esta acción eliminará permanentemente a{' '}
              <strong className="text-[#1A1A1A]">
                {deleteDialog.user.nombres} {deleteDialog.user.apellidos}
              </strong>.
              {' '}No se puede deshacer.
            </>
          ) : null
        }
        confirmText="Eliminar"
        cancelText="Cancelar"
        variant="danger"
        isLoading={isDeleting}
        onConfirm={handleConfirmDelete}
        onClose={handleCancelDelete}
      />
    </>
  );
}
