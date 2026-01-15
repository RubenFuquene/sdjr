'use client';

/**
 * Componente Coordinador de Perfiles
 * 
 * Responsabilidad única: Coordinar tabs y layout general
 * 
 * Delega responsabilidades:
 * - Roles → RolesView (auto-contenido)
 * - Proveedores → ProvidersTable (presentación)
 * - Usuarios → UsersTable (presentación)
 * - Administradores → AdministratorsTable (presentación)
 */

import { useState, useCallback, ReactNode } from 'react';
import { Users, Store, UserCog } from 'lucide-react';
import { Vista, Usuario, Administrador } from '@/types/admin';
import { RolesView } from './roles';
import { ProvidersView } from './providers/providers-view';
import { UsersTable } from './users/users-table';
import { AdministratorsTable } from './administrators/administrators-table';
import { PageHeader } from '@/components/admin/shared/page-header';

interface ProfilesContentProps {
  usuarios: Usuario[];
  administradores: Administrador[];
}

export function ProfilesContent({
  usuarios,
  administradores,
}: ProfilesContentProps) {
  const [vista, setVista] = useState<Vista>('perfiles');
  const [headerActions, setHeaderActions] = useState<ReactNode | null>(null);

  const handleVistaChange = (newVista: Vista) => {
    setVista(newVista);
  };

  // Setter estable para permitir que las vistas hijas "renten" el espacio de acciones del header
  const handleSetHeaderActions = useCallback((node: ReactNode | null) => {
    setHeaderActions(node ?? null);
  }, []);

  return (
    <div className="space-y-6 pb-8">
      {/* Header con título y descripción */}
      <PageHeader>
        <PageHeader.Content>
          <PageHeader.Title>Gestión de Perfiles</PageHeader.Title>
          <PageHeader.Description>
            Administra roles, proveedores, usuarios y administradores del sistema
          </PageHeader.Description>
        </PageHeader.Content>
        {headerActions ? (
          <PageHeader.Actions>
            {headerActions}
          </PageHeader.Actions>
        ) : null}
      </PageHeader>

      {/* Tabs */}
      <div className="bg-white rounded-[18px] shadow-sm p-4 border border-slate-100">
        <div className="flex flex-wrap gap-4">
          <button
            onClick={() => handleVistaChange('perfiles')}
            className={`flex items-center gap-2 px-4 py-3 rounded-xl transition ${
              vista === 'perfiles'
                ? 'bg-[#4B236A] text-white shadow-lg'
                : 'text-[#6A6A6A] hover:bg-[#F7F7F7]'
            }`}
          >
            <Users className="w-5 h-5" />
            Perfiles
          </button>
          <button
            onClick={() => handleVistaChange('proveedores')}
            className={`flex items-center gap-2 px-4 py-3 rounded-xl transition ${
              vista === 'proveedores'
                ? 'bg-[#4B236A] text-white shadow-lg'
                : 'text-[#6A6A6A] hover:bg-[#F7F7F7]'
            }`}
          >
            <Store className="w-5 h-5" />
            Proveedores
          </button>
          <button
            onClick={() => handleVistaChange('usuarios')}
            className={`flex items-center gap-2 px-4 py-3 rounded-xl transition ${
              vista === 'usuarios'
                ? 'bg-[#4B236A] text-white shadow-lg'
                : 'text-[#6A6A6A] hover:bg-[#F7F7F7]'
            }`}
          >
            <Users className="w-5 h-5" />
            Usuarios
          </button>
          <button
            onClick={() => handleVistaChange('administradores')}
            className={`flex items-center gap-2 px-4 py-3 rounded-xl transition ${
              vista === 'administradores'
                ? 'bg-[#4B236A] text-white shadow-lg'
                : 'text-[#6A6A6A] hover:bg-[#F7F7F7]'
            }`}
          >
            <UserCog className="w-5 h-5" />
            Administradores
          </button>
        </div>
      </div>

      {/* Vistas delegadas */}
      {vista === 'perfiles' && <RolesView onSetHeaderActions={handleSetHeaderActions} />}
      {vista === 'proveedores' && <ProvidersView onSetHeaderActions={handleSetHeaderActions} />}
      {vista === 'usuarios' && <UsersTable data={usuarios} />}
      {vista === 'administradores' && <AdministratorsTable data={administradores} />}
    </div>
  );
}
