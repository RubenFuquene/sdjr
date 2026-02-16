'use client';

/**
 * Vista Especializada de Administradores
 *
 * Responsabilidad unica: Gestionar UI de administradores
 * - Renderizar tabla de administradores
 * - Mostrar loading y error states
 * - Filtrado local por nombre y perfil
 */

import { useMemo, useState, useCallback, ReactNode, useEffect } from 'react';
import { Plus } from 'lucide-react';
import type { Administrador, Usuario } from '@/types/admin';
import type { CreateUserPayload, UpdateUserPayload } from '@/types/user';
import { useAdministratorManagement } from '@/hooks/use-administrator-management';
import { useRoleManagement } from '@/hooks/use-role-management';
import { useUserManagement } from '@/hooks/use-user-management';
import { AdministratorsTable } from './administrators-table';
import { ProfilesFilters } from '../profiles-filters';
import { TableLoadingState } from '@/components/admin/shared/loading-state';
import { ErrorState } from '@/components/admin/shared/error-state';
import { ConfirmationDialog } from '@/components/admin/shared/confirmation-dialog';
import { AdministratorCreationModal, UserVisualizationModal } from '@/components/admin/modals';

interface AdministratorsViewProps {
  onSetHeaderActions?: (node: ReactNode | null) => void;
}

interface AppliedFilters {
  searchTerm: string;
  perfilFilter: string;
}

function mapAdminToUsuario(admin: Administrador): Usuario {
  return {
    id: admin.id,
    nombres: admin.nombres,
    apellidos: admin.apellidos,
    celular: '',
    email: admin.correo,
    perfil: admin.perfil,
    activo: admin.activo,
  };
}

export function AdministratorsView({
  onSetHeaderActions,
}: AdministratorsViewProps) {
  const { administradores, loading, error, refresh } = useAdministratorManagement();
  const roleManagement = useRoleManagement();
  const userManagement = useUserManagement();

  const [searchTerm, setSearchTerm] = useState('');
  const [perfilFilter, setPerfilFilter] = useState('todos');
  const [appliedFilters, setAppliedFilters] = useState<AppliedFilters>({
    searchTerm: '',
    perfilFilter: 'todos',
  });
  const [isCreateModalOpen, setIsCreateModalOpen] = useState(false);
  const [deleteDialog, setDeleteDialog] = useState<{
    isOpen: boolean;
    admin: Administrador | null;
  }>({ isOpen: false, admin: null });
  const [isDeleting, setIsDeleting] = useState(false);
  const [modalState, setModalState] = useState<{
    isOpen: boolean;
    mode: 'view' | 'edit';
    usuario: Usuario | null;
  }>({ isOpen: false, mode: 'view', usuario: null });

  const perfiles = useMemo(() => {
    const unique = Array.from(new Set(administradores.map((admin) => admin.perfil).filter(Boolean)));
    return unique.map((nombre, idx) => ({ id: idx, nombre }));
  }, [administradores]);

  const filteredAdministradores = useMemo(() => {
    let list: Administrador[] = administradores;

    if (appliedFilters.perfilFilter !== 'todos') {
      list = list.filter((admin) => admin.perfil === appliedFilters.perfilFilter);
    }

    const search = appliedFilters.searchTerm.trim().toLowerCase();
    if (search) {
      list = list.filter((admin) => {
        const fullName = `${admin.nombres} ${admin.apellidos}`.toLowerCase();
        const correo = admin.correo.toLowerCase();
        const area = admin.area.toLowerCase();
        const perfil = admin.perfil.toLowerCase();

        return (
          fullName.includes(search) ||
          correo.includes(search) ||
          area.includes(search) ||
          perfil.includes(search)
        );
      });
    }

    return list;
  }, [administradores, appliedFilters]);

  const handleSearch = useCallback(() => {
    setAppliedFilters({
      searchTerm,
      perfilFilter,
    });
  }, [searchTerm, perfilFilter]);

  const handleRetry = useCallback(async () => {
    try {
      await refresh();
    } catch (err) {
      console.error('Error al reintentar administradores:', err);
    }
  }, [refresh]);

  const handleCreateClick = useCallback(() => {
    setIsCreateModalOpen(true);
  }, []);

  const handleCloseModal = useCallback(() => {
    setIsCreateModalOpen(false);
  }, []);

  const handleSaveNewAdministrator = useCallback(
    async (payload: CreateUserPayload) => {
      try {
        // Usar hook para crear usuario (maneja API + refresh)
        await userManagement.handleCreate(payload);
        // Refrescar lista de administradores
        await refresh();
        // Cerrar modal
        setIsCreateModalOpen(false);
      } catch (error) {
        console.error('Error al crear administrador:', error);
        // Re-lanzar error para que el modal lo maneje
        throw error;
      }
    },
    [userManagement, refresh]
  );

  const handleViewAdmin = useCallback((admin: Administrador) => {
    setModalState({
      isOpen: true,
      mode: 'view',
      usuario: mapAdminToUsuario(admin),
    });
  }, []);

  const handleEditAdmin = useCallback((admin: Administrador) => {
    setModalState({
      isOpen: true,
      mode: 'edit',
      usuario: mapAdminToUsuario(admin),
    });
  }, []);

  const handleToggleAdmin = useCallback(
    async (admin: Administrador) => {
      try {
        await userManagement.handleToggle(admin.id);
        await refresh();
      } catch (error) {
        console.error('Error al cambiar estado del administrador:', error);
      }
    },
    [userManagement, refresh]
  );

  const handleDeleteAdmin = useCallback(
    (admin: Administrador) => {
      setDeleteDialog({ isOpen: true, admin });
    },
    []
  );

  const handleConfirmDelete = useCallback(async () => {
    if (!deleteDialog.admin) return;

    try {
      setIsDeleting(true);
      await userManagement.handleDelete(deleteDialog.admin.id);
      await refresh();
      setDeleteDialog({ isOpen: false, admin: null });
    } catch (error) {
      console.error('Error al eliminar administrador:', error);
    } finally {
      setIsDeleting(false);
    }
  }, [deleteDialog.admin, userManagement, refresh]);

  const handleCancelDelete = useCallback(() => {
    setDeleteDialog({ isOpen: false, admin: null });
  }, []);

  const handleModalClose = useCallback(() => {
    setModalState({ isOpen: false, mode: 'view', usuario: null });
  }, []);

  const handleModalSave = useCallback(
    async (updatedUsuario: Usuario) => {
      try {
        const payload: UpdateUserPayload = {
          name: updatedUsuario.nombres,
          last_name: updatedUsuario.apellidos,
          email: updatedUsuario.email,
          phone: updatedUsuario.celular,
          roles: [updatedUsuario.perfil],
          status: updatedUsuario.activo ? 'A' : 'I',
        };

        await userManagement.handleUpdate(updatedUsuario.id, payload);
        await refresh();
        handleModalClose();
      } catch (error) {
        console.error('Error al actualizar administrador:', error);
        throw error;
      }
    },
    [userManagement, refresh, handleModalClose]
  );

  // Obtener roles disponibles desde el sistema
  const availableRoles = useMemo(() => {
    return roleManagement.roles.map((role) => role.nombre);
  }, [roleManagement.roles]);

  const adminRoles = useMemo(() => {
    return availableRoles.filter((role) => role.toLowerCase().includes('admin'));
  }, [availableRoles]);

  useEffect(() => {
    if (!onSetHeaderActions) return;

    const buttonClass =
      'flex items-center gap-2 px-4 h-[52px] bg-[#4B236A] text-white rounded-xl hover:bg-[#5D2B7D] transition shadow-lg hover:shadow-xl';
    const button = (
      <button onClick={handleCreateClick} className={buttonClass}>
        <Plus size={20} />
        <span>Agregar Administrador</span>
      </button>
    );

    onSetHeaderActions(button);

    return () => onSetHeaderActions(null);
  }, [onSetHeaderActions, handleCreateClick]);

  if (error) {
    return <ErrorState message={error} onRetry={handleRetry} />;
  }

  if (loading) {
    return <TableLoadingState />;
  }

  if (filteredAdministradores.length === 0) {
    return (
      <div className="flex flex-col items-center justify-center py-12">
        <h3 className="text-lg font-medium text-[#1A1A1A] mb-2">No hay administradores</h3>
        <p className="text-sm text-[#6A6A6A] mb-6">No hay administradores registrados</p>
      </div>
    );
  }

  return (
    <>
      <div className="space-y-6">
        <ProfilesFilters
          vista="administradores"
          searchTerm={searchTerm}
          perfilFilter={perfilFilter}
          perfiles={perfiles}
          onSearchChange={setSearchTerm}
          onPerfilChange={setPerfilFilter}
          onSearch={handleSearch}
        />

        <AdministratorsTable
          data={filteredAdministradores}
          onView={handleViewAdmin}
          onEdit={handleEditAdmin}
          onToggle={handleToggleAdmin}
          onDelete={handleDeleteAdmin}
        />
      </div>

      <AdministratorCreationModal
        isOpen={isCreateModalOpen}
        availableRoles={availableRoles}
        onClose={handleCloseModal}
        onSave={handleSaveNewAdministrator}
      />

      <UserVisualizationModal
        isOpen={modalState.isOpen}
        mode={modalState.mode}
        usuario={modalState.usuario}
        roles={adminRoles}
        onClose={handleModalClose}
        onSave={handleModalSave}
      />

      <ConfirmationDialog
        isOpen={deleteDialog.isOpen}
        title="Eliminar administrador"
        description={
          deleteDialog.admin
            ? (
              <>
                Estas a punto de eliminar a{' '}
                <strong>{deleteDialog.admin.nombres} {deleteDialog.admin.apellidos}</strong>.
                {' '}No se puede deshacer.
              </>
            )
            : null
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
