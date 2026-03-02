"use client";

import { useState } from "react";
import { toast } from "sonner";
import { BranchesPageHeader } from "./branches-page-header";
import { BranchesPageContent } from "./branches-page-content";
import { BranchFormModal } from "./branch-form-modal";
import { ConfirmationDialog } from "@/components/admin/shared/confirmation-dialog";
import { useProviderBranchForm } from "@/hooks/provider/use-provider-branch-form";
import { useProviderBranches } from "@/hooks/provider/use-provider-branches";
import type { ProviderBranchFormInput } from "@/hooks/provider/use-provider-branch-form";
import type { ProviderBranchCardViewModel } from "@/types/provider-branches";
import type { BranchFormInitialData, BranchFormMode } from "./branch-form";
import { ApiError, updateCommerceBranch } from "@/lib/api";

export function BranchesPageClient() {
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [modalMode, setModalMode] = useState<BranchFormMode>("create");
  const [editingBranchId, setEditingBranchId] = useState<number | null>(null);
  const [editingInitialData, setEditingInitialData] = useState<BranchFormInitialData | null>(null);
  const [editError, setEditError] = useState<string | null>(null);
  const [editing, setEditing] = useState(false);
  const [branchPendingDeletion, setBranchPendingDeletion] = useState<ProviderBranchCardViewModel | null>(null);

  const {
    branches,
    loading,
    error,
    hasCommerce,
    currentPage,
    lastPage,
    totalBranches,
    refresh,
    setPage,
  } = useProviderBranches();

  const {
    isSubmitting,
    error: creationError,
    fieldErrors,
    submitForm,
    clearFormErrors,
  } = useProviderBranchForm();

  const mapBranchToInitialData = (branch: (typeof branches)[number]): BranchFormInitialData => ({
    name: branch.name,
    address: branch.address,
    phone: branch.phone,
    email: branch.email,
    departmentName: branch.department,
    cityName: branch.city,
    neighborhoodName: branch.neighborhood,
    hours: branch.hours,
  });

  const openCreateModal = () => {
    setModalMode("create");
    setEditingBranchId(null);
    setEditingInitialData(null);
    setEditError(null);
    clearFormErrors();
    setIsModalOpen(true);
  };

  const closeModal = () => {
    setModalMode("create");
    setEditingBranchId(null);
    setEditingInitialData(null);
    setEditError(null);
    clearFormErrors();
    setIsModalOpen(false);
  };

  const handleSubmitBranchForm = async (input: ProviderBranchFormInput) => {
    if (modalMode === "edit" && editingBranchId) {
      try {
        setEditError(null);
        setEditing(true);

        await updateCommerceBranch(editingBranchId, {
          name: input.name,
          address: input.address,
          phone: input.phone,
          email: input.email,
          department_id: input.departmentId,
          city_id: input.cityId,
          neighborhood_id: input.neighborhoodId,
          status: input.status,
        });

        await refresh();
        closeModal();
        toast.success("Sucursal actualizada correctamente");
      } catch (error) {
        if (error instanceof ApiError) {
          if (error.status === 403) {
            setEditError("No tienes permisos para editar sucursales.");
            return;
          }

          setEditError(error.message || "No pudimos actualizar la sucursal.");
          return;
        }

        setEditError("No pudimos actualizar la sucursal. Inténtalo de nuevo.");
      } finally {
        setEditing(false);
      }

      return;
    }

    const created = await submitForm(input, {
      onSuccess: async () => {
        await refresh();
      },
    });

    if (created) {
      clearFormErrors();
      setIsModalOpen(false);
      toast.success("Sucursal creada correctamente");
    }
  };

  const handleEditBranch = (branch: ProviderBranchCardViewModel) => {
    const sourceBranch = branches.find((item) => item.id === branch.id);

    if (!sourceBranch) {
      toast.error("No encontramos la información de esta sucursal para editar.");
      return;
    }

    setModalMode("edit");
    setEditingBranchId(sourceBranch.id);
    setEditingInitialData(mapBranchToInitialData(sourceBranch));
    setEditError(null);
    clearFormErrors();
    setIsModalOpen(true);
  };

  const handleDeleteBranch = (branch: ProviderBranchCardViewModel) => {
    setBranchPendingDeletion(branch);
  };

  const handleCloseDeleteDialog = () => {
    setBranchPendingDeletion(null);
  };

  const handleConfirmDeleteBranch = async () => {
    if (!branchPendingDeletion) {
      return;
    }

    toast.info(`Eliminación de ${branchPendingDeletion.name} pendiente de integración backend.`);
    setBranchPendingDeletion(null);
  };

  return (
    <>
      <BranchesPageHeader onAddBranch={openCreateModal} />

      <BranchesPageContent
        branches={branches}
        loading={loading}
        error={error}
        hasCommerce={hasCommerce}
        totalBranches={totalBranches}
        currentPage={currentPage}
        lastPage={lastPage}
        onPageChange={setPage}
        onAddBranch={openCreateModal}
        onEditBranch={handleEditBranch}
        onDeleteBranch={handleDeleteBranch}
      />

      <BranchFormModal
        isOpen={isModalOpen}
        mode={modalMode}
        initialData={editingInitialData}
        submitting={isSubmitting || editing}
        error={modalMode === "edit" ? editError : creationError}
        fieldErrors={modalMode === "edit" ? {} : fieldErrors}
        onClose={closeModal}
        onSubmit={handleSubmitBranchForm}
      />

      <ConfirmationDialog
        isOpen={Boolean(branchPendingDeletion)}
        title="Eliminar sucursal"
        description={
          branchPendingDeletion
            ? `¿Deseas eliminar la sucursal ${branchPendingDeletion.name}? Esta acción se conectará al backend en la siguiente fase.`
            : undefined
        }
        confirmText="Eliminar"
        cancelText="Cancelar"
        variant="danger"
        onClose={handleCloseDeleteDialog}
        onConfirm={handleConfirmDeleteBranch}
      />
    </>
  );
}
