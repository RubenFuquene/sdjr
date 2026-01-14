'use client';

/**
 * Vista Especializada de Proveedores
 * 
 * Responsabilidad única: Gestionar UI de proveedores
 * - Renderizar tabla de proveedores
 * - Manejar modales (creación/edición/vista)
 * - Mostrar loading y error states
 * - Delegar datos al componente presentacional ProvidersTable
 * 
 * Patrón: Layout State Composition (similar a RolesView)
 */

import { useState, useCallback, ReactNode, useMemo, useEffect } from 'react';
import { Plus } from 'lucide-react';
import { ProveedorListItem, Proveedor, Perfil } from '@/types/admin';
import { useCommerceManagement } from '@/hooks/use-commerce-management';
import { ProvidersTable } from './providers-table';
import { ProfilesFilters } from '../profiles-filters';
import { TableLoadingState } from '@/components/admin/shared/loading-state';
import { ErrorState } from '@/components/admin/shared/error-state';
import { ProviderVisualizationModal } from '@/components/admin/modals';

/**
 * Props de la vista
 */
interface ProvidersViewProps {
  onSetHeaderActions?: (node: ReactNode | null) => void;
}

/**
 * Vista de Proveedores - Auto-contenida
 * 
 * Estados:
 * - Loading: Tabla vacía con skeleton
 * - Error: Mensaje de error con botón de reintentar
 * - Empty: Mensaje cuando no hay proveedores
 * - Success: Tabla con proveedores y acciones
 */
export function ProvidersView({ 
  onSetHeaderActions,
}: ProvidersViewProps) {
  // Hook de gestión de comercios
  const commerceManagement = useCommerceManagement();

  // Estado de filtros locales
  const [searchTerm, setSearchTerm] = useState('');
  const [perfilFilter, setPerfilFilter] = useState('todos');

  // Estado del modal
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [modalMode, setModalMode] = useState<'view' | 'edit'>('view');
  const [selectedProvider, setSelectedProvider] = useState<Proveedor | null>(null);
  const [isLoadingProvider, setIsLoadingProvider] = useState(false);

  // Opciones de perfiles (únicos) derivadas de los proveedores
  const perfiles = useMemo(() => {
    const unique = Array.from(new Set(commerceManagement.commerces.map((p) => p.perfil).filter(Boolean)));
    return unique.map((nombre, idx) => ({ id: idx, nombre }));
  }, [commerceManagement.commerces]);

  /**
   * Abre modal de vista (solo lectura)
   */
  const handleViewProvider = useCallback(async (proveedor: ProveedorListItem) => {
    try {
      setIsLoadingProvider(true);
      // TODO: Fetch proveedor completo desde API
      // const fullProvider = await getCommerce(proveedor.id);
      
      // Por ahora, convertir ProveedorListItem a Proveedor con datos mock
      const fullProvider: Proveedor = {
        ...proveedor,
        nit: '900123456-7',
        tipoEstablecimiento: 'Comercial',
        departamento: '',
        ciudad: '',
        barrio: '',
        direccion: '',
        verificado: false,
        documentos: [],
        sucursales: [],
      };
      
      setSelectedProvider(fullProvider);
      setModalMode('view');
      setIsModalOpen(true);
    } catch (error) {
      console.error('Error al cargar proveedor:', error);
    } finally {
      setIsLoadingProvider(false);
    }
  }, []);

  /**
   * Abre modal de edición
   */
  const handleEditProvider = useCallback(async (proveedor: ProveedorListItem) => {
    try {
      setIsLoadingProvider(true);
      // TODO: Fetch proveedor completo desde API
      // const fullProvider = await getCommerce(proveedor.id);
      
      // Por ahora, convertir ProveedorListItem a Proveedor con datos mock
      const fullProvider: Proveedor = {
        ...proveedor,
        nit: '900123456-7',
        tipoEstablecimiento: 'Comercial',
        departamento: '',
        ciudad: '',
        barrio: '',
        direccion: '',
        verificado: false,
        documentos: [],
        sucursales: [],
      };
      
      setSelectedProvider(fullProvider);
      setModalMode('edit');
      setIsModalOpen(true);
    } catch (error) {
      console.error('Error al cargar proveedor:', error);
    } finally {
      setIsLoadingProvider(false);
    }
  }, []);

  /**
   * Activa/Desactiva proveedor
   */
  const handleToggleProvider = useCallback(async (proveedor: ProveedorListItem) => {
    try {
      await commerceManagement.handleToggle(proveedor.id);
    } catch (error) {
      console.error('Error al cambiar estado del proveedor:', error);
    }
  }, [commerceManagement]);

  /**
   * Elimina proveedor
   */
  const handleDeleteProvider = useCallback(async (proveedor: ProveedorListItem) => {
    try {
      await commerceManagement.handleDelete(proveedor.id);
    } catch (error) {
      console.error('Error al eliminar proveedor:', error);
    }
  }, [commerceManagement]);

  /**
   * Busca proveedores con los filtros actuales
   */
  const handleSearch = useCallback(async () => {
    try {
      await commerceManagement.handleSearch({
        search: searchTerm || undefined,
        status: perfilFilter !== 'todos' ? perfilFilter : undefined,
      });
    } catch (error) {
      console.error('Error al buscar proveedores:', error);
    }
  }, [searchTerm, perfilFilter, commerceManagement]);

  /**
   * Crea un nuevo proveedor
   */
  const handleCreateClick = useCallback(() => {
    console.log('Abrir modal de creación de proveedor');
    // TODO: Implementar modal de creación
  }, []);

  /**
   * Header Actions: Botón "Crear Proveedor"
   */
  useEffect(() => {
    if (!onSetHeaderActions) return;

    const buttonClass = 'flex items-center gap-2 px-4 h-[52px] bg-[#4B236A] text-white rounded-xl hover:bg-[#5D2B7D] transition shadow-lg hover:shadow-xl';
    const button = (
      <button onClick={handleCreateClick} className={buttonClass}>
        <Plus size={20} />
        <span>Crear Proveedor</span>
      </button>
    );

    onSetHeaderActions(button);

    return () => onSetHeaderActions(null);
  }, [onSetHeaderActions, handleCreateClick]);

  /**
   * Reintentar cargar datos en caso de error
   */
  const handleRetry = useCallback(async () => {
    try {
      await commerceManagement.refresh();
    } catch (error) {
      console.error('Error al reintentar:', error);
    }
  }, [commerceManagement]);

  /**
   * Cierra el modal
   */
  const handleCloseModal = useCallback(() => {
    setIsModalOpen(false);
    setSelectedProvider(null);
  }, []);

  /**
   * Guarda los cambios del modal
   */
  const handleSaveModal = useCallback(async (updatedProvider: Proveedor) => {
    try {
      // TODO: Implementar guardado real con API
      console.log('Guardar proveedor:', updatedProvider);
      await commerceManagement.handleUpdate(updatedProvider.id, updatedProvider);
      setIsModalOpen(false);
      setSelectedProvider(null);
    } catch (error) {
      console.error('Error al guardar proveedor:', error);
      throw error;
    }
  }, [commerceManagement]);

  // Estados
  if (commerceManagement.error) {
    return <ErrorState message={commerceManagement.error} onRetry={handleRetry} />;
  }

  if (commerceManagement.loading) {
    return <TableLoadingState />;
  }

  if (commerceManagement.commerces.length === 0) {
    return (
      <div className="flex flex-col items-center justify-center py-12">
        <h3 className="text-lg font-medium text-[#1A1A1A] mb-2">No hay proveedores</h3>
        <p className="text-sm text-[#6A6A6A] mb-6">No hay proveedores registrados</p>
      </div>
    );
  }

  return (
    <>
      <div className="space-y-6">
        {/* Filtros */}
        <ProfilesFilters 
          vista={"proveedores"}
          searchTerm={searchTerm}
          perfilFilter={perfilFilter}
          perfiles={perfiles}
          onSearchChange={setSearchTerm}
          onPerfilChange={setPerfilFilter}
          onSearch={handleSearch}
        />

        {/* Tabla de Proveedores */}
        <ProvidersTable
          data={commerceManagement.commerces}
          onView={handleViewProvider}
          onEdit={handleEditProvider}
          onToggle={handleToggleProvider}
          onDelete={handleDeleteProvider}
        />
      </div>

      {/* Modal de Visualización/Edición */}
      <ProviderVisualizationModal
        isOpen={isModalOpen}
        mode={modalMode}
        proveedor={selectedProvider}
        perfiles={perfiles as Perfil[]}
        onClose={handleCloseModal}
        onSave={handleSaveModal}
      />
    </>
  );
}
