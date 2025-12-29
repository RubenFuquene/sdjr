"use client";

import { useState } from "react";
import { Users, Store, UserCog, Plus, UserPlus } from "lucide-react";
import { Vista, Proveedor, Usuario, Administrador, Perfil } from "@/types/admin";
import { CreateRoleRequest } from "@/types/role-form-types";
import { adaptPermissions } from "@/components/admin/adapters/permission-adapter";
import { useRoles } from "@/hooks/use-roles";
import { ProfilesFilters } from "./profiles-filters";
import { ProfilesTable } from "./profiles/profiles-table";
import { ProvidersTable } from "@/components/admin/management/providers/providers-table";
import { UsersTable } from "@/components/admin/management/users/users-table";
import { AdministratorsTable } from "@/components/admin/management/administrators/administrators-table";
import { TableLoadingState } from "@/components/admin/shared/loading-state";
import { ErrorState } from "@/components/admin/shared/error-state";
import { PageHeader } from "@/components/admin/shared/page-header";
import { RoleCreationModal } from "@/components/admin/modals";

interface ProfilesContentProps {
  proveedores: Proveedor[];
  usuarios: Usuario[];
  administradores: Administrador[];
}

export function ProfilesContent({
  proveedores,
  usuarios,
  administradores,
}: ProfilesContentProps) {
  const [vista, setVista] = useState<Vista>("perfiles");
  const [searchTerm, setSearchTerm] = useState("");
  const [perfilFilter, setPerfilFilter] = useState("todos");
  const [isRoleModalOpen, setIsRoleModalOpen] = useState(false);
  const [modalMode, setModalMode] = useState<'create' | 'edit' | 'view'>('create');
  const [selectedRole, setSelectedRole] = useState<{id: number; name: string; description: string; permissions: string[]} | undefined>(undefined);
  
  // Cargar roles desde API
  const { roles: perfiles, loading, error, refresh } = useRoles();

  // Función para adaptar Perfil a formato del modal
  const adaptProfileToRole = (perfil: Perfil) => {
    // Combinar permisos de admin y proveedor (3 niveles)
    const allPermissions3Levels = [...perfil.permisosAdmin, ...perfil.permisosProveedor];
    
    // Convertir a permisos de 4 niveles para el árbol interno del modal
    const mockPermissions = allPermissions3Levels.map(perm => ({ name: perm, description: perm }));
    const adapted4Levels = adaptPermissions(mockPermissions);
    
    return {
      id: perfil.id,
      name: perfil.nombre,
      description: perfil.descripcion,
      permissions: adapted4Levels.map(p => p.name) // Permisos de 4 niveles para el árbol
    };
  };

  const handleSearch = () => {
    console.log("Buscando:", { vista, searchTerm, perfilFilter });
    // TODO: Implementar lógica de filtrado real
  };

  const handleVistaChange = (newVista: Vista) => {
    setVista(newVista);
    setSearchTerm("");
    setPerfilFilter("todos");
  };

  const handleCreate = () => {
    if (vista === "perfiles") {
      setModalMode('create');
      setSelectedRole(undefined);
      setIsRoleModalOpen(true);
    } else {
      console.log(`Crear en vista: ${vista}`);
      // TODO: Implementar otros modales de creación
    }
  };

  const handleViewRole = (role: Perfil) => {
    setModalMode('view');
    setSelectedRole(adaptProfileToRole(role));
    setIsRoleModalOpen(true);
  };

  const handleEditRole = (role: Perfil) => {
    setModalMode('edit');
    setSelectedRole(adaptProfileToRole(role));
    setIsRoleModalOpen(true);
  };

  const handleCreateRole = async (roleData: CreateRoleRequest) => {
    try {
      if (modalMode === 'edit' && selectedRole) {
        // TODO: Implementar llamada real a API PUT /api/v1/roles/{id}
        console.log('🚀 Editando rol:', roleData, 'ID:', selectedRole.id);
      } else {
        // TODO: Implementar llamada real a API POST /api/v1/roles
        console.log('🚀 Creando rol:', roleData);
      }
      
      // Simular llamada API
      await new Promise(resolve => setTimeout(resolve, 1000));
      
      console.log('✅ Rol', modalMode === 'edit' ? 'editado' : 'creado', 'exitosamente');
      
      // Refrescar la lista de roles
      refresh();
      
      // Cerrar modal
      setIsRoleModalOpen(false);
      setSelectedRole(undefined);
      
    } catch (error) {
      console.error('❌ Error al', modalMode === 'edit' ? 'editar' : 'crear', 'rol:', error);
      throw error; // Re-lanzar para que el modal maneje el error
    }
  };

  const renderActionButton = () => {
    const buttonClass = "flex items-center gap-2 px-4 h-[52px] bg-[#4B236A] text-white rounded-xl hover:bg-[#5D2B7D] transition shadow-lg hover:shadow-xl";
    
    switch (vista) {
      case "perfiles":
        return (
          <button onClick={handleCreate} className={buttonClass}>
            <Plus className="w-5 h-5" />
            Crear Rol
          </button>
        );
      case "administradores":
        return (
          <button onClick={handleCreate} className={buttonClass}>
            <UserPlus className="w-5 h-5" />
            Agregar Administrador
          </button>
        );
      default:
        return null;
    }
  };

  return (
    <div className="space-y-6">
      {/* Header con título, descripción y acciones dinámicas */}
      <PageHeader>
        <PageHeader.Content>
          <PageHeader.Title>Gestión de Perfiles</PageHeader.Title>
          <PageHeader.Description>
            Administra perfiles, proveedores, usuarios y administradores del sistema
          </PageHeader.Description>
        </PageHeader.Content>
        <PageHeader.Actions>
          {renderActionButton()}
        </PageHeader.Actions>
      </PageHeader>
      
      {/* Tabs */}
      <div className="bg-white rounded-[18px] shadow-sm p-4 border border-slate-100">
        <div className="flex flex-wrap gap-4">
          <button
            onClick={() => handleVistaChange("perfiles")}
            className={`flex items-center gap-2 px-4 py-3 rounded-xl transition ${
              vista === "perfiles"
                ? "bg-[#4B236A] text-white shadow-lg"
                : "text-[#6A6A6A] hover:bg-[#F7F7F7]"
            }`}
          >
            <Users className="w-5 h-5" />
            Perfiles
          </button>
          <button
            onClick={() => handleVistaChange("proveedores")}
            className={`flex items-center gap-2 px-4 py-3 rounded-xl transition ${
              vista === "proveedores"
                ? "bg-[#4B236A] text-white shadow-lg"
                : "text-[#6A6A6A] hover:bg-[#F7F7F7]"
            }`}
          >
            <Store className="w-5 h-5" />
            Proveedores
          </button>
          <button
            onClick={() => handleVistaChange("usuarios")}
            className={`flex items-center gap-2 px-4 py-3 rounded-xl transition ${
              vista === "usuarios"
                ? "bg-[#4B236A] text-white shadow-lg"
                : "text-[#6A6A6A] hover:bg-[#F7F7F7]"
            }`}
          >
            <Users className="w-5 h-5" />
            Usuarios
          </button>
          <button
            onClick={() => handleVistaChange("administradores")}
            className={`flex items-center gap-2 px-4 py-3 rounded-xl transition ${
              vista === "administradores"
                ? "bg-[#4B236A] text-white shadow-lg"
                : "text-[#6A6A6A] hover:bg-[#F7F7F7]"
            }`}
          >
            <UserCog className="w-5 h-5" />
            Administradores
          </button>
        </div>
      </div>

      {/* Filtros */}
      <ProfilesFilters
        vista={vista}
        searchTerm={searchTerm}
        perfilFilter={perfilFilter}
        perfiles={perfiles}
        onSearchChange={setSearchTerm}
        onPerfilChange={setPerfilFilter}
        onSearch={handleSearch}
      />

      {/* Tablas con estados de loading y error */}
      {vista === "perfiles" && (
        <>
          {loading && <TableLoadingState />}
          {error && <ErrorState message={error} onRetry={refresh} />}
          {!loading && !error && perfiles.length === 0 && (
            <div className="text-center py-12 text-[#6A6A6A]">
              No se encontraron perfiles
            </div>
          )}
          {!loading && !error && perfiles.length > 0 && (
            <ProfilesTable 
              data={perfiles} 
              onView={handleViewRole}
              onEdit={handleEditRole}
            />
          )}
        </>
      )}
      {vista === "proveedores" && <ProvidersTable data={proveedores} />}
      {vista === "usuarios" && <UsersTable data={usuarios} />}
      {vista === "administradores" && <AdministratorsTable data={administradores} />}

      {/* Modal de Creación/Edición/Vista de Roles */}
      <RoleCreationModal
        isOpen={isRoleModalOpen}
        mode={modalMode}
        role={selectedRole}
        onClose={() => {
          setIsRoleModalOpen(false);
          setSelectedRole(undefined);
        }}
        onSave={handleCreateRole}
      />
    </div>
  );
}
